<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Snapshots\CanTakeSnapshot;
use Spatie\EventProjector\Snapshots\Snapshot;
use Spatie\EventProjector\Snapshots\Snapshottable;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;

class SnapshottableProjector implements Projector, Snapshottable
{
    use ProjectsEvents, CanTakeSnapshot;

    public $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
        MoneySubtracted::class => 'onMoneySubtracted',
    ];

    public function onMoneyAdded(MoneyAdded $event)
    {
        $event->account->addMoney($event->amount);
    }

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        $event->account->subtractMoney($event->amount);
    }

    public function writeToSnapshot(Snapshot $snapshot)
    {
        $snapshot->write('test');
    }

    public function restoreSnapshot(Snapshot $snapshot)
    {
        // TODO: Implement restoreSnapshot() method.
    }
}
