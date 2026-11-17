<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithArray;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\TestEvent;

class EventHandlingMethodsProjector extends Projector
{
    public static array $log = [];

    public function onMoneyAdded(MoneyAddedEvent $event): void
    {
        static::$log[] = 'onMoneyAdded';
    }

    public function onMoneyChanged(MoneyAddedEvent|MoneySubtractedEvent $event): void
    {
        static::$log[] = 'onMoneyChanged';
    }

    public function onOptionalTestEvent(?TestEvent $event): void
    {
        static::$log[] = 'onOptionalTestEvent';
    }

    private function onEventWithArray(EventWithArray $event): void
    {
        static::$log[] = 'onEventWithArray';
    }
}
