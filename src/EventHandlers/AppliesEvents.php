<?php

namespace Spatie\EventSourcing\EventHandlers;

use Spatie\EventSourcing\Handlers;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

trait AppliesEvents
{
    protected function apply(StoredEvent ...$storedEvents): void
    {
        foreach ($storedEvents as $storedEvent) {
            $this->applyStoredEvent($storedEvent);
        }
    }

    private function applyStoredEvent(StoredEvent $storedEvent)
    {
        Handlers::find($storedEvent->event, $this)->each(
            fn (string $handler) => $this->{$handler}($storedEvent->event)
        );
    }
}
