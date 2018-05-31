<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Snapshots\Snapshot;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Snapshots\Snapshottable;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Snapshots\CanTakeSnapshot;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;

class SnapshottableThatDoesNotWriteAnythingProjector implements Projector, Snapshottable
{
    use ProjectsEvents, CanTakeSnapshot;

    public $handlesEvents = [
    ];


    public function writeToSnapshot(Snapshot $snapshot)
    {

    }

    public function restoreSnapshot(Snapshot $snapshot)
    {

    }
}
