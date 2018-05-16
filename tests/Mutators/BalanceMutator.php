<?php

namespace Spatie\EventSaucer\Tests\Mutators;

use Spatie\EventSaucer\Tests\Events\MoneyAdded;
use Spatie\EventSaucer\Tests\Events\MoneySubtracted;

class BalanceMutator
{
    public $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
        MoneySubtracted::class => 'onMoneySubtracted',
    ];

    public function onMoneyAdded(MoneyAdded $event) // + ioc arguments
    {
        $event->account->addMoney($event->amount);
    }

    public function onMoneySubtracted(MoneySubtracted $event) // + ioc arguments
    {
        $event->account->subtractMoney($event->amount);
    }
}