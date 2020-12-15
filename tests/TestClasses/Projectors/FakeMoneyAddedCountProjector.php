<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class FakeMoneyAddedCountProjector extends Projector
{
    public static int $eventsHandledCount = 0;

    protected array $handlesEvents = [
        MoneyAddedEvent::class => 'onMoneyAdded',
    ];

    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        self::$eventsHandledCount++;
    }
}
