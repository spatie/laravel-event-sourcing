<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;

class ResettableProjector extends Projector
{
    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        $event->account->addMoney($event->amount);
    }

    public function reset()
    {
        Account::truncate();
    }
}
