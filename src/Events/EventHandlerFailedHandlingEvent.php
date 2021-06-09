<?php

namespace Spatie\EventSourcing\Events;

use Exception;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

class EventHandlerFailedHandlingEvent
{
    public function __construct(
        public EventHandler $eventHandler,
        public StoredEvent $storedEvent,
        public Exception $exception
    ) {
    }
}
