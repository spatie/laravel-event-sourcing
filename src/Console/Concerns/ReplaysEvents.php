<?php

namespace Spatie\EventProjector\Console\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Models\ProjectorStatus;

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
        $projectorsWithoutStatus = collect($projectors)
            ->filter(function (Projector $projector) {
                return ! ProjectorStatus::query()
                        ->where('projector_name', $projector->getName())
                        ->exists();
            });

        if ($projectorsWithoutStatus->isNotEmpty()) {
            return 0;
        }

        $allProjectorStatusesCount = DB::table('projector_statuses')
            ->whereIn('projector_name', $projectors->map->getName()->toArray())
            ->count();

        $allUpToDateProjectorStatusesCount = DB::table('projector_statuses')
            ->whereIn('projector_name', $projectors->map->getName()->toArray())
            ->where('has_received_all_events', true)
            ->count();

        if ($allProjectorStatusesCount === $allUpToDateProjectorStatusesCount) {
            return StoredEvent::getMaxId();
        }

        return DB::table('projector_statuses')
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
}
