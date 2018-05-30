<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Console\Command;

class DeleteSnapshotCommand extends Command
{
    protected $signature = 'event-projector:delete-snapshot';

    protected $description = 'Delete snapshots';

    /** @var \Illuminate\Support\Collection */
    protected  $snapshots;

    public function __construct()
    {
        $this->snapshots = $this->snapshotRepository->get();
    }

    public function handle()
    {
        if ($this->snapshots->isEmpty()) {
            $this->warn("There currently are no snapshots. You can take a snapshot by running `php artisan event-projector:create-snapshot`.");

            return;
        }

        $this->displaySnapshots();

        $snapshotNumber = $this->ask('Which snapshot would you like to delete?');

        if (! $snapshot = $this->snapshots->get($snapshotNumber)) {
            $this->error("There is no snapshot for that number.");

            return;
        }

        $snapshot->delete();

        $this->comment("Snapshot number {$snapshotNumber} deleted!");
    }

    public function displaySnapshots()
    {
        $titles = ['Number', 'Projector', 'Last processed event id', 'Created at', 'Name'];

        $rows = $this->snapshots->map(function (Snapshot $snapshot, int $index) {
            return [
                $index,
                $snapshot->projectorName(),
                $snapshot->lastProcessedEventId(),
                $snapshot->createdAt(),
                $snapshot->name(),
            ];
        });

        $this->table($titles, $rows);
    }
}
