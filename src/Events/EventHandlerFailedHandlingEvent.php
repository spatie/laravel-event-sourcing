<?php

namespace Spatie\EventSourcing\Events;

use Exception;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\StoredEvent;

final class EventHandlerFailedHandlingEvent
{
    /** @var \Spatie\EventSourcing\EventHandlers\EventHandler */
    public EventHandler $eventHandler;

    /** @var \Spatie\EventSourcing\Models\EloquentStoredEvent */
    public StoredEvent $storedEvent;

    /** @var \Exception */
    public Exception $exception;

    public function __construct(EventHandler $eventHandler, StoredEvent $storedEvent, Exception $exception)
    {
        $this->eventHandler = $eventHandler;

        $this->storedEvent = $storedEvent;

        $this->exception = $exception;
    }
}
