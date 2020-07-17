<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class AddMoneyToAccount
{
    public function __invoke(MoneyAddedEvent $event)
    {
        $event->account->addMoney($event->amount);
    }
}

class ProjectorThatInvokesAnObject extends Projector
{
    protected $handlesEvents = [
        MoneyAddedEvent::class => AddMoneyToAccount::class,
    ];
}
