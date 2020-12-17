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
    public static bool $singleAttributeEventHandled = false;
    public static bool $multiAttributeEventHandled = false;

    #[Handles(MoneyAddedEvent::class)]
    public function handleSingleAttributeEvent($event): void
    {
        self::$handledEvents[$event::class] = $event;
        self::$singleAttributeEventHandled = true;
    }

    #[Handles(MoneySubtractedEvent::class, MoneyAddedEventWithQueueOverride::class, MoneyAddedEvent::class)]
    public function handleMultiAttributeEvent($event): void
    {
        self::$handledEvents[$event::class] = $event;
		self::$multiAttributeEventHandled = true;
    }
}
