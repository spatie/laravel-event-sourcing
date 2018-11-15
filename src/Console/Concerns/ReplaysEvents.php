<?php

namespace Spatie\EventProjector\Console\Concerns;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;

trait ReplaysEvents
{
    use ProjectsEvents;

    public function replay(Collection $projectors)
    {
        $afterEventId = $this->determineAfterEventId($projectors);

        if ($afterEventId === $this->getStoredEventClass()::getMaxId()) {
            $this->warn('There are no events to replay.');
        }

        $replayCount = $this->getStoredEventClass()::after($afterEventId)->count();

        if ($replayCount === 0) {
            $this->warn('There are no events to replay');

            return;
        }

        $afterEventId === 0
            ? $this->comment('Replaying all events...')
            : $this->comment("Replaying events after stored event id {$afterEventId}...");
        $this->emptyLine();

        $bar = $this->output->createProgressBar($this->getStoredEventClass()::after($afterEventId)->count());
        $onEventReplayed = function () use ($bar) {
            $bar->advance();
        };

        $this->projectionist->replay($projectors, $afterEventId, $onEventReplayed);

        $bar->finish();

        $this->emptyLine(2);
        $this->comment('All done!');
    }

    protected function determineAfterEventId(Collection $projectors): int
    {
        $projectorsWithoutStatus = collect($projectors)
            ->filter(function (Projector $projector) {
                return ! $this->getProjectorStatusClass()::query()
                    ->where('projector_name', $projector->getName())
                    ->exists();
            });

        if ($projectorsWithoutStatus->isNotEmpty()) {
            return 0;
        }

        $allProjectorStatusesCount = $this->getProjectorStatusClass()::query()
            ->whereIn('projector_name', $projectors->map->getName()->toArray())
            ->count();

        $allUpToDateProjectorStatusesCount = $this->getProjectorStatusClass()::query()
            ->whereIn('projector_name', $projectors->map->getName()->toArray())
            ->where('has_received_all_events', true)
            ->count();

        if ($allProjectorStatusesCount === $allUpToDateProjectorStatusesCount) {
            return $this->getStoredEventClass()::getMaxId();
        }

        return $this->getProjectorStatusClass()::query()
                ->whereIn('projector_name', $projectors->map->getName()->toArray())
                ->where('has_received_all_events', false)
                ->min('last_processed_event_id') ?? 0;
    }

    protected function emptyLine(int $amount = 1)
    {
        foreach (range(1, $amount) as $i) {
            $this->line('');
        }
    }

    protected function getStoredEventClass(): string
    {
        return config('event-projector.stored_event_model');
    }
}
