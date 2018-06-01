<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Console\Command;
use Spatie\EventProjector\Snapshots\Snapshot;
use Spatie\EventProjector\Snapshots\SnapshotRepository;
use Spatie\EventProjector\Console\Snapshots\Concerns\ChooseSnapshot;

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

        $this->comment('Snapshot deleted!');
    }
}
