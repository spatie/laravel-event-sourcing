<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;

class AccountProjector extends Projector
{
    public function onMoneyAdded(MoneyAdded $event)
    {
        $account = Account::firstOrCreate(['uuid' => $event->aggregateRootUuid()]);

        $account->amount += $event->amount;

        $account->save();
    }
}
