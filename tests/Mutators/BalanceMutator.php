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

    public function onMoneyAdded(MoneyAdded $event)
    {
        $event->account->addMoney($event->amount);
    }

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        $event->account->subtractMoney($event->amount);
    }
}