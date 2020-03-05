<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors;

use Spatie\EventSourcing\Projectors\Projector;
use Spatie\EventSourcing\Projectors\ProjectsEvents;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\DummyEvent;

class GetMetaDataProjector implements Projector
{
    use ProjectsEvents;

    public static string $foundAggregateRootUuid = '';

    public function onDummyEvent(DummyEvent $event)
    {
        static::$foundAggregateRootUuid = $event->aggregateRootUuid();
    }
}
