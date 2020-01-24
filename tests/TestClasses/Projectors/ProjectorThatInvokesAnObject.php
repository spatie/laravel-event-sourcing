<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Projectors\Projector;
use Spatie\EventSourcing\Projectors\ProjectsEvents;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class AddMoneyToAccount
{
    public function __invoke(MoneyAddedEvent $event)
    {
        $event->account->addMoney($event->amount);
    }
}

class ProjectorThatInvokesAnObject implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAddedEvent::class => AddMoneyToAccount::class,
    ];
}
