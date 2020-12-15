<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Attributes\ListensTo;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\EmptyAccountEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEventWithQueueOverride;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;

class AttributeProjector extends Projector
{
    public static array $handledEvents = [];

    #[ListensTo(MoneyAddedEvent::class)]
    public function handleSingleAttributeEvent($event)
    {
        self::$handledEvents[$event::class] = $event;
    }

    #[
        ListensTo(MoneySubtractedEvent::class),
        ListensTo(MoneyAddedEventWithQueueOverride::class),
    ]
    public function handleMultiAttributeEvent($event)
    {
        self::$handledEvents[$event::class] = $event;
    }
}
