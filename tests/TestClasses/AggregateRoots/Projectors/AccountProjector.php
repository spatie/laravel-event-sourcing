<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;

class AccountProjector extends Projector
{
    protected array $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
    ];

    public function onMoneyAdded(MoneyAdded $event, string $aggregateUuid)
    {
        $account = Account::firstOrCreate(['uuid' => $aggregateUuid]);

        $account->amount += $event->amount;

        $account->save();
    }
}
