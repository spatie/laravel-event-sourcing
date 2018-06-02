<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Snapshots\Snapshot;
use Spatie\EventProjector\Snapshots\Snapshottable;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;

class SnapshottableProjector extends BalanceProjector implements Snapshottable
{
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

        foreach ($unserializedAccounts as $accountAttributes) {
            Account::create($accountAttributes);
        }
    }
}
