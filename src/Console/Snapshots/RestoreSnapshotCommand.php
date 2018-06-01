<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Console\Command;
use Spatie\EventProjector\Console\Snapshots\Concerns\ChooseSnapshot;
use Spatie\EventProjector\Snapshots\SnapshotRepository;

class RestoreSnapshotCommand extends Command
{
    use ChooseSnapshot;

    protected $signature = 'event-projector:restore-snapshot';

    protected $description = 'Restore a snapshot';

    /** @var \Spatie\EventProjector\Console\Snapshots\SnapshotRepository */
    protected $snapshotRepository;

    public function __construct(SnapshotRepository $snapshotRepository)
    {
        parent::__construct();

        $this->snapshotRepository = $snapshotRepository;
    }

    public function handle()
    {
        $snapshots = $this->snapshotRepository->get();

        $snapshot = $this->chooseSnapshot('Which snapshot number would like to load?', $snapshots);

        if (! $snapshot) {
            return;
        }

        $snapshot->restore();

        $this->info('Snapshot restored!');
    }
}
