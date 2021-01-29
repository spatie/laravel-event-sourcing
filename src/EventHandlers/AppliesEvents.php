<?php

namespace Spatie\EventSourcing\EventHandlers;

use Spatie\EventSourcing\Handlers;
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

        Handlers::find($event, $this)->each(
            fn (string $handler) => $this->{$handler}($event)
        );
    }
}
