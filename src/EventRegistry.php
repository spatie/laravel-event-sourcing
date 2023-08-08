<?php

namespace Spatie\EventSourcing;

use Illuminate\Support\Collection;
use Spatie\EventSourcing\Exceptions\InvalidStorableEvent;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class EventRegistry
{
    protected Collection $classMap;

    public function __construct(array $eventClasses = [])
    {
        $this->classMap = new Collection();

        $this->addEventClasses($eventClasses);
    }

    public function addEventClass(string $eventClass, string $alias = null): static
    {
        if (! is_subclass_of($eventClass, ShouldBeStored::class)) {
            throw InvalidStorableEvent::notAStorableEventClassName($eventClass);
        }

        if (! $this->classMap->containsStrict($eventClass)) {
            $this->classMap->put($eventClass::eventName($alias), $eventClass);
        }

        return $this;
    }

    public function addEventClasses(array $eventClasses): static
    {
        foreach ($eventClasses as $alias => $eventClass) {
            $this->addEventClass($eventClass, is_int($alias) ? null : $alias);
        }

        return $this;
    }

    public function getEventClass(string $alias): string
    {
        return $this->classMap->get($alias, $alias);
    }

    public function getAlias(string $eventClass): string
    {
        return $this->classMap->search($eventClass, true) ?: $eventClass;
    }

    public function setClassMap(array $classMap): static
    {
        $this->classMap = new Collection($classMap);

        return $this;
    }

    public function getClassMap(): Collection
    {
        return $this->classMap;
    }
}
