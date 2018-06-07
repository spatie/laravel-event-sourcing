<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Snapshots\Snapshot;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Snapshots\Snapshottable;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Snapshots\CanTakeSnapshot;

class SnapshottableThatDoesNotWriteAnythingProjector implements Projector, Snapshottable
{
    use ProjectsEvents, CanTakeSnapshot;

    protected $handlesEvents = [
    ];

    public function writeToSnapshot(Snapshot $snapshot)
    {
    }

    public function restoreFromSnapshot(Snapshot $snapshot)
    {
    }
}
