<?php

namespace Spatie\EventSourcing\AggregateRoots;

use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\EventHandlers\AppliesEvents;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

abstract class AggregatePartial
{
    use AppliesEvents;

    public function __construct(
        protected AggregateRoot $aggregateRoot
    ) {
    }

    protected function aggregateRootUuid(): string
    {
        return $this->aggregateRoot->uuid();
    }

    protected function recordThat(ShouldBeStored $event): static
    {
        $this->aggregateRoot->recordThat($event);

        return $this;
    }

    protected function recordConcurrently(ShouldBeStored $domainEvent, ?callable $allowConcurrent = null): static
    {
        $this->aggregateRoot->recordConcurrently($domainEvent, $allowConcurrent);

        return $this;
    }

    public function apply(StoredEvent | ShouldBeStored ...$storedEvents): void
    {
        foreach ($storedEvents as $storedEvent) {
            $this->applyStoredEvent($storedEvent);
        }
    }

    public static function fake(): static
    {
        $aggregateRoot = FakeAggregateRootForPartial::retrieve(Uuid::uuid4()->toString());

        $partial = new static($aggregateRoot);

        $aggregateRoot->addPartial($partial);

        return $partial;
    }
}
