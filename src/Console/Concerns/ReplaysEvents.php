<?php

namespace Spatie\EventProjector\Console\Concerns;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;

trait ReplaysEvents
{
    public function replayEvents(Collection $projectors)
    {
        $afterEventId = $this->determineAfterEventId($projectors);

        if ($afterEventId === StoredEvent::getMaxId()) {
            $this->warn('There are no events to replay.');
        }

        $replayEventsCount = StoredEvent::after($afterEventId)->count();

        if ($replayEventsCount === 0) {
            $this->warn('There are no events to replay');

            return;
        }

        $afterEventId === 0
            ? $this->comment('Replaying all events...')
            : $this->comment("Replaying events after stored event id {$afterEventId}...");
        $this->emptyLine();

        $bar = $this->output->createProgressBar(StoredEvent::after($afterEventId)->count());
        $onEventReplayed = function () use ($bar) {
            $bar->advance();
        };

        $this->eventProjectionist->replayEvents($projectors, $afterEventId, $onEventReplayed);

        $bar->finish();

        $this->emptyLine(2);
        $this->comment('All done!');
    }

    protected function determineAfterEventId(Collection $projectors): int
    {
        return $projectors
            ->map(function ($projector) {
                if (is_string($projector)) {
                    $projector = app($projector);
                }

                return $projector;
            })
            ->map(function (Projector $projector) {
                return $projector->getLastProcessedEventId();
            })
            ->min();
    }

    protected function emptyLine(int $amount = 1)
    {
        foreach (range(1, $amount) as $i) {
            $this->line('');
        }
    }
}