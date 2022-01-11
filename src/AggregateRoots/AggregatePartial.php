<?php

namespace Spatie\EventSourcing\AggregateRoots;

use Ramsey\Uuid\Uuid;
use ReflectionClass;
use ReflectionProperty;
use Spatie\EventSourcing\Attributes\IncludeInSnapshot;
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

    public function getState(): array
    {
        $class = new ReflectionClass($this);

        return collect($class->getProperties(ReflectionProperty::IS_PUBLIC))
            ->reject(fn (ReflectionProperty $reflectionProperty) => $reflectionProperty->isStatic())
            ->mapWithKeys(function (ReflectionProperty $property) {
                return [$property->getName() => $this->{$property->getName()}];
            })
            ->merge(
                collect($class->getProperties(ReflectionProperty::IS_PROTECTED))
                    ->filter(fn (ReflectionProperty $reflectionProperty) => !empty($reflectionProperty->getAttributes(IncludeInSnapshot::class)))
                    ->mapWithKeys(function (ReflectionProperty $property) {
                        return [$property->getName() => $this->{$property->getName()}];
                    })
            )
            ->merge([])->toArray();
    }

    public function useState(array $state): void
    {
        foreach ($state as $key => $value) {
            $this->$key = $value;
        }
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
        $aggregateRoot = FakeAggregateRootForPartial::retrieve(Uuid::uuid4()->toString());

        $partial = new static($aggregateRoot);

        $aggregateRoot->addPartial($partial);

        return $partial;
    }
}
