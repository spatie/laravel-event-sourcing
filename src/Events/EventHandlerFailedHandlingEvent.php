<?php

namespace Spatie\EventProjector\Events;

use Exception;
use Spatie\EventProjector\Models\StoredEventData;
use Spatie\EventProjector\EventHandlers\EventHandler;

final class EventHandlerFailedHandlingEvent
{
    /** @var \Spatie\EventProjector\EventHandlers\EventHandler */
    public $eventHandler;

    /** @var \Spatie\EventProjector\Models\StoredEvent */
    public $storedEvent;

    /** @var \Exception */
    public $exception;

    public function __construct(EventHandler $eventHandler, StoredEventData $storedEvent, Exception $exception)
    {
        $this->eventHandler = $eventHandler;

        $this->storedEvent = $storedEvent;

        $this->exception = $exception;
    }
}
