<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;

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
