<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Attributes\ListensTo;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\EmptyAccountEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;

class AttributeProjector extends Projector
{
    #[ListensTo(MoneyAddedEvent::class)]
    public function onMoneyAdded(object $event)
    {
        $event->account->addMoney($event->amount);
    }

    #[
        ListensTo(MoneySubtractedEvent::class),
        ListensTo(EmptyAccountEvent::class),
    ]
    public function onMoneySubtracted(object $event)
    {
        $event->account->subtractMoney($event->amount);
    }
}
