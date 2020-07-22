<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\DummyEvent;

class GetMetaDataProjector extends Projector
{
    public static string $foundAggregateRootUuid = '';

    public function onDummyEvent(DummyEvent $event)
    {
        static::$foundAggregateRootUuid = $event->aggregateRootUuid();
    }
}
