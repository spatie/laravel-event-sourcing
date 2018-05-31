<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Console\Snapshots\Concerns\ChooseSnapshot;
use Spatie\EventProjector\Snapshots\Snapshot;
use Spatie\EventProjector\Snapshots\SnapshotRepository;

class DeleteSnapshotCommand extends Command
{
    use ChooseSnapshot;

    protected $signature = 'event-projector:delete-snapshot';

    protected $description = 'Delete a snapshot';

    /** @var \Spatie\EventProjector\Snapshots\SnapshotRepository */
    protected $snapshotRepository;

    public function __construct(SnapshotRepository $snapshotRepository)
    {
        parent::__construct();

        $this->snapshotRepository = $snapshotRepository;
    }

    public function handle()
    {
        $snapshots = $this->snapshotRepository->get();

        $snapshot = $this->chooseSnapshot('Which snapshot number would like to delete?', $snapshots);

        if (! $snapshot) {
            return;
        }

        $snapshot->delete();

        $this->comment("Snapshot deleted!");
    }

    public function displaySnapshots(Collection $snapshots)
    {
        $titles = ['Number', 'Projector', 'Last processed event id', 'Created at', 'Name'];

        $rows = $snapshots->map(function (Snapshot $snapshot, int $index) {
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
