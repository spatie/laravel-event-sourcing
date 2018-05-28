<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Console\Command;
use Spatie\EventProjector\Snapshots\Snapshot;
use Spatie\EventProjector\Snapshots\SnapshotRepository;

class ListSnapshotsCommand extends Command
{
    protected $signature = 'event-projector:list-snapshots';

    protected $description = 'List all snapshots';

    /** @var \Spatie\EventProjector\Snapshots\SnapshotRepository */
    protected $snapshotRepository;

    public function __construct(SnapshotRepository $snapshotRepository)
    {
        parent::__construct();

        $this->snapshotRepository = $snapshotRepository;
    }

    public function handle()
    {
        $titles = ['Projector', 'Last processed event id', 'Created at', 'Name'];

        $rows = $this->snapshotRepository->get()->map(function(Snapshot $snapshot) {
            return [
                $snapshot->projectorName(),
                $snapshot->lastProcessedEventId(),
                $snapshot->createdAt(),
                $snapshot->name(),
            ];
        });

        $this->table($titles, $rows);
    }
}
