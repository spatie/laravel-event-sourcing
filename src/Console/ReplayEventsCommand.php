<?php

namespace Spatie\EventSorcerer\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\EventSorcerer\EventSorcerer;
use Spatie\EventSorcerer\Exceptions\InvalidMutator;
use Spatie\EventSorcerer\StoredEvent;

class ReplayEventsCommand extends Command
{
    protected $signature = 'event-sorcerer:replay-events 
                            {--mutator=*} : The mutator that should receive the event';

    protected $description = 'Replay stored events';

    /** @var \Spatie\EventSorcerer\EventSorcerer */
    protected $eventSorcerer;

    public function __construct(EventSorcerer $eventSorcerer)
    {
        $this->eventSorcerer = $eventSorcerer;
    }

    public function handle()
    {
        $mutators = $this->getMutators();

        if (count($mutators)) {
            $this->warn("No mutators found to replay events to");

            return;
        }

        $this->comment('Replaying events...');

        StoredEvent::chunk(1000, function (StoredEvent $storedEvent) use ($mutators)  {
            $this->eventSorcerer->callEventHandlers($mutators, $storedEvent);
        });

        $this->comment('All done!');
    }

    protected function getMutators(): Collection
    {
        $onlyCallMutators = $this->option('mutator');

        $this->guardAgainstNonExistingMutators($onlyCallMutators);

        return $this->eventSorcerer->mutators
            ->filter(function (string $mutator) use ($onlyCallMutators) {
                if (!count($onlyCallMutators)) {
                    return true;
                }

                return in_array($mutator, $onlyCallMutators);
            });
    }

    protected function guardAgainstNonExistingMutators(array $onlyCallMutators)
    {
        foreach ($onlyCallMutators as $mutator) {
            if (!class_exists($mutator)) {
                throw InvalidMutator::doesNotExist($mutator);
            }
        }
    }
}
