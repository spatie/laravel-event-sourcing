<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Snapshots\Snapshot;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Snapshots\Snapshottable;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Snapshots\CanTakeSnapshot;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;

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
        $serializableAccounts = Account::get()->each->toArray();

        $serializedAccounts = json_encode($serializableAccounts);

        $snapshot->write($serializedAccounts);
    }

    public function restoreSnapshot(Snapshot $snapshot)
    {
        $serializedAccounts = $snapshot->read();

        $unserializedAccounts = json_decode($serializedAccounts, true);

        foreach($unserializedAccounts as $accountAttributes) {
            Account::create($accountAttributes);
        }
    }
}
