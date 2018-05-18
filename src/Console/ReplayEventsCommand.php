<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\EventProjector\StoredEvent;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;

class ReplayEventsCommand extends Command
{
    protected $signature = 'event-projector:replay-events 
                            {--projector=*} : The projector that should receive the event';

    protected $description = 'Replay stored events';

    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventSorcerer;

    /** @var string */
    protected $storedEventModelClass;

    public function __construct(EventProjectionist $eventSorcerer, string $storedEventModelClass)
    {
        parent::__construct();

        $this->eventSorcerer = $eventSorcerer;

        $this->storedEventModelClass = $storedEventModelClass;
    }

    public function handle()
    {
        $projectors = $this->getProjectors();

        if ($projectors->isEmpty()) {
            $this->warn('No projectors found to replay events to...');

            return;
        }

        $this->comment('Replaying events...');

        $bar = $this->output->createProgressBar(StoredEvent::count());

        StoredEvent::chunk(1000, function (Collection $storedEvents) use ($projectors, $bar) {
            $storedEvents->each(function (StoredEvent $storedEvent) use ($projectors, $bar) {
                $this->eventSorcerer->callEventHandlers($projectors, $storedEvent->event);
                $bar->advance();
            });
        });

        $bar->finish();

        $this->comment('All done!');
    }

    protected function getProjectors(): Collection
    {
        $onlyCallProjectors = $this->option('projector');

        $this->guardAgainstNonExistingProjectors($onlyCallProjectors);

        return $this->eventSorcerer->projectors
            ->filter(function ($projector) use ($onlyCallProjectors) {
                if (! is_string($projector)) {
                    $projector = get_class($projector);
                }

                return in_array($projector, $onlyCallProjectors);
            });
    }

    protected function guardAgainstNonExistingProjectors(array $onlyCallProjectors)
    {
        foreach ($onlyCallProjectors as $projector) {
            if (! class_exists($projector)) {
                throw InvalidEventHandler::doesNotExist($projector);
            }
        }
    }
}
