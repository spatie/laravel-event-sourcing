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
                            {--mutator=*} : The mutator that should receive the event';

    protected $description = 'Replay stored events';

    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventSorcerer;

    public function __construct(EventProjectionist $eventSorcerer)
    {
        parent::__construct();

        $this->eventSorcerer = $eventSorcerer;
    }

    public function handle()
    {
        $mutators = $this->getMutators();

        if ($mutators->isEmpty()) {
            $this->warn('No mutators found to replay events to...');

            return;
        }

        $this->comment('Replaying events...');

        $bar = $this->output->createProgressBar(StoredEvent::count());

        StoredEvent::chunk(1000, function (StoredEvent $storedEvent) use ($mutators, $bar) {
            $this->eventSorcerer->callEventHandlers($mutators, $storedEvent);

            $bar->advance();
        });

        $bar->finish();

        $this->comment('All done!');
    }

    protected function getMutators(): Collection
    {
        $onlyCallMutators = $this->option('mutator');

        $this->guardAgainstNonExistingMutators($onlyCallMutators);

        return $this->eventSorcerer->mutators
            ->filter(function (string $mutator) use ($onlyCallMutators) {
                if (! count($onlyCallMutators)) {
                    return true;
                }

                return in_array($mutator, $onlyCallMutators);
            });
    }

    protected function guardAgainstNonExistingMutators(array $onlyCallMutators)
    {
        foreach ($onlyCallMutators as $mutator) {
            if (! class_exists($mutator)) {
                throw InvalidEventHandler::doesNotExist($mutator);
            }
        }
    }
}
