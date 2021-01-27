<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class AttributeProjector extends Projector
{
    public static array $handledEvents = [];

    public function handleSingleAttributeEvent($event): void
    {
        self::$handledEvents[$event::class] = $event;
    }

    public function handleMultiAttributeEvent($event): void
    {
        self::$handledEvents[$event::class] = $event;
    }
}
