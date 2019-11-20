<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors;

use Spatie\EventSourcing\Projectors\Projector;
use Spatie\EventSourcing\Projectors\ProjectsEvents;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;

final class AccountProjector implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
    ];

    public function onMoneyAdded(MoneyAdded $event, string $aggregateUuid)
    {
        $account = Account::firstOrCreate(['uuid' => $aggregateUuid]);

        $account->amount += $event->amount;

        $account->save();
    }
}
