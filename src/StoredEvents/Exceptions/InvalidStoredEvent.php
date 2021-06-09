<?php

namespace Spatie\EventSourcing\StoredEvents\Exceptions;

use Exception;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

class InvalidStoredEvent extends Exception
{
    public static function couldNotUnserializeEvent(StoredEvent $storedEvent, Exception $serializerException): self
    {
        return new static(
            "Failed to unserialize an event with class `{$storedEvent->event_class}` on stored event with id `{$storedEvent->id}`. Are you sure that event class exists? ",
            previous:  $serializerException
        );
    }
}
