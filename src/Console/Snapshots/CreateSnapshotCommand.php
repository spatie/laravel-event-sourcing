<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Console\Command;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Snapshots\SnapshotFactory;
use Spatie\EventProjector\Snapshots\Snapshotter;

class CreateSnapshotCommand extends Command
{
    protected $signature = 'event-projector:create-snapshot {projectorName} {--name}';

    protected $description = 'Create new snapshots';

    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    /** @var \Spatie\EventProjector\Snapshots\SnapshotFactory */
    protected $snapshotFactory;

    public function __construct(EventProjectionist $eventProjectionist, SnapshotFactory $snapshotFactory)
    {
        parent::__construct();

        $this->eventProjectionist = $eventProjectionist;

        $this->snapshotFactory = $snapshotFactory;
    }

    public function handle()
    {
        $projectorName = $this->argument('projectorName');

        $projector = $this->eventProjectionist->getProjector($projectorName);

        if (! $projector) {
            $this->warn("No projector named `{$projectorName}` found!");

            return;
        }

        $this->snapshotFactory->createForProjector($projector, $this->option('name'));
    }
}

