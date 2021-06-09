<?php

namespace Spatie\EventSourcing\EventHandlers;

use Exception;
use Illuminate\Support\Collection;
use Spatie\EventSourcing\Handlers;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

trait HandlesEvents
{
    public function handles(): array
    {
        return $this->getEventHandlingMethods()->keys()->toArray();
    }

    public function handle(StoredEvent $storedEvent)
    {
        $eventClass = $storedEvent->event_class;

        $handlersForEvent = $this->getEventHandlingMethods()->get($eventClass);

        foreach ($handlersForEvent as $handler) {
            $this->{$handler}($storedEvent->event);
        }
    }

    public function handleException(Exception $exception): void
    {
        report($exception);
    }

    public function getEventHandlingMethods(): Collection
    {
        return Handlers::list($this);
    }
}
