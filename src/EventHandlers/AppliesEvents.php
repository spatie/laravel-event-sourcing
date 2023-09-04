<?php

namespace Spatie\EventSourcing\EventHandlers;

use Spatie\BetterTypes\Handlers;
use Spatie\BetterTypes\Method;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

trait AppliesEvents
{
    protected function apply(StoredEvent | ShouldBeStored ...$storedEvents): void
    {
        foreach ($storedEvents as $storedEvent) {
            $this->applyStoredEvent($storedEvent);
        }
    }

    private function applyStoredEvent(StoredEvent | ShouldBeStored $event)
    {
        $event = $event instanceof StoredEvent
            ? $event->event
            : $event;

        Handlers::new($this)
            ->public()
            ->protected()
            ->reject(fn (Method $method) => in_array($method->getName(), ['apply', 'recordThat']))
            ->reject(fn (Method $method) => $method->accepts(null))
            ->accepts($event)
            ->all()
            ->each(
                fn (Method $method) => $this->{$method->getName()}($event)
            );
    }
}
