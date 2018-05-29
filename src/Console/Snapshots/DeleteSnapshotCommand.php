<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Console\Command;

class DeleteSnapshotCommand extends Command
{
    protected $signature = 'event-projector:delete-snapshot';

    protected $description = 'Delete snapshots';

    public function handle()
    {
        $titles = ['Number', 'Projector', 'Last processed event id', 'Created at', 'Name'];

        $rows = $this->snapshotRepository->get()->map(function (Snapshot $snapshot) {
            static $number = 0;
            return [
                $number++,
                $snapshot->projectorName(),
                $snapshot->lastProcessedEventId(),
                $snapshot->createdAt(),
                $snapshot->name(),
            ];
        });

        $this->table($titles, $rows);

        $snapshotNumber = $this->ask('Which snapshot would you like to delete?');
    }
}
