<?php

namespace Spatie\EventSourcing\AggregateRoots;

use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\EventHandlers\AppliesEvents;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

abstract class AggregateEntity
{
    use AppliesEvents;

    public function __construct(
        private AggregateRoot $aggregateRoot
    ) {
    }

    protected function aggregateRootUuid(): string
    {
        return $this->aggregateRoot->uuid();
    }

    protected function recordThat(ShouldBeStored $event): void
    {
        $this->aggregateRoot->recordThat($event);
    }

    public function apply(StoredEvent | ShouldBeStored ...$storedEvents): void
    {
        foreach ($storedEvents as $storedEvent) {
            $this->applyStoredEvent($storedEvent);
        }
    }

    public static function fake(): static
    {
        $aggregateRoot = FakeAggregateRootForEntity::retrieve(Uuid::uuid4()->toString());

        $entity = new static($aggregateRoot);

        $aggregateRoot->addEntity($entity);

        return $entity;
    }
}
