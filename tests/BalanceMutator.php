<?php

namespace Spatie\EventSaucer\Tests;

use Spatie\EventSaucer\Tests\Events\MoneyAdded;

class BalanceMutator
{
    protected $events = [
        MoneyAdded::class => 'onMoneyAdded',
    ];

    public function __construct()
    {
        // from ioc
    }

    public function onMoneyAdded(MoneyAdded $event) // + ioc arguments
    {
        $event->account->addMoney($event->amount);
    }
}