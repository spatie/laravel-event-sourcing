<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Attributes\Handles;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEventWithQueueOverride;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;

class AttributeProjector extends Projector
{
    public static array $handledEvents = [];

    #[Handles(MoneyAddedEvent::class)]
    public function handleSingleAttributeEvent($event): void
    {
        self::$handledEvents[$event::class] = $event;
    }

    #[Handles(MoneySubtractedEvent::class, MoneyAddedEventWithQueueOverride::class)]
    public function handleMultiAttributeEvent($event): void
    {
        self::$handledEvents[$event::class] = $event;
    }
}
