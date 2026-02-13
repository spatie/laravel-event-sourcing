<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class EventWithShouldBeStoredPropertyNames extends ShouldBeStored
{
    public function __construct(
        public readonly string $id,
        public readonly string $createdAt,
        public readonly string $aggregateRootUuid,
    ) {
    }
}
