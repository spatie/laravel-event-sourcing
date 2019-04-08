<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtractedEvent;

class BalanceProjector implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAddedEvent::class => 'onMoneyAdded',
        MoneySubtractedEvent::class => 'onMoneySubtracted',
    ];

    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        $event->account->addMoney($event->amount);
    }

    public function onMoneySubtracted(MoneySubtractedEvent $event)
    {
        $event->account->subtractMoney($event->amount);
    }

    public function onStartingEventReplay()
    {
    }

    public function onFinishedEventReplay()
    {
    }
}
