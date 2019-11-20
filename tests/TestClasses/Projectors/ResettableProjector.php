<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Projectors\Projector;
use Spatie\EventSourcing\Projectors\ProjectsEvents;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;

final class ResettableProjector implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAddedEvent::class => 'onMoneyAdded',
    ];

    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        $event->account->addMoney($event->amount);
    }

    public function resetState()
    {
        Account::truncate();
    }
}
