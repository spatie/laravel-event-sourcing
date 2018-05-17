<?php

namespace Spatie\EventSorcerer\Tests\TestClasses\Mutators;

use Spatie\EventSorcerer\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventSorcerer\Tests\TestClasses\Events\MoneySubtracted;

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
