<?php

namespace Spatie\EventSourcing\Events;

use Exception;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

class EventHandlerFailedHandlingEvent
{
    public EventHandler $eventHandler;

    public StoredEvent $storedEvent;

    public Exception $exception;

    public function __construct(EventHandler $eventHandler, StoredEvent $storedEvent, Exception $exception)
    {
        $this->eventHandler = $eventHandler;

        $this->storedEvent = $storedEvent;

        $this->exception = $exception;
    }
}
