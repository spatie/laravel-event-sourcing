<?php

namespace Spatie\EventSourcing\Events;

use Exception;
use Spatie\EventSourcing\StoredEvent;
use Spatie\EventSourcing\EventHandlers\EventHandler;

final class EventHandlerFailedHandlingEvent
{
    /** @var \Spatie\EventSourcing\EventHandlers\EventHandler */
    public $eventHandler;

    /** @var \Spatie\EventSourcing\Models\EloquentStoredEvent */
    public $storedEvent;

    /** @var \Exception */
    public $exception;

    public function __construct(EventHandler $eventHandler, StoredEvent $storedEvent, Exception $exception)
    {
        $this->eventHandler = $eventHandler;

        $this->storedEvent = $storedEvent;

        $this->exception = $exception;
    }
}
