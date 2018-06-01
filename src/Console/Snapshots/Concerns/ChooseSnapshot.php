<?php

namespace Spatie\EventProjector\Console\Snapshots\Concerns;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Snapshots\Snapshot;

trait ChooseSnapshot
{
    public function chooseSnapshot(string $question, Collection $snapshots): ?Snapshot
    {
        if ($snapshots->isEmpty()) {
            $this->warn('There currently are no snapshots. You can take a snapshot by running `php artisan event-projector:create-snapshot`.');

            return null;
        }

        $this->displaySnapshots($snapshots);

        $snapshotNumber = $this->ask($question);

        if (! $snapshot = $snapshots->get($snapshotNumber - 1)) {
            $this->error('There is no snapshot for that number.');

            return null;
        }

        return $snapshot;
    }

    public function displaySnapshots(Collection $snapshots)
    {
        $titles = ['Number', 'Projector', 'Last processed event id', 'Created at', 'Name'];

        $rows = $snapshots->map(function (Snapshot $snapshot, int $index) {
            return [
                $index + 1,
                $snapshot->projectorName(),
                $snapshot->lastProcessedEventId(),
                $snapshot->createdAt(),
                $snapshot->name(),
            ];
        });

        $this->table($titles, $rows);
    }
}
