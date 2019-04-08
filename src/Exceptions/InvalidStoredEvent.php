<?php

namespace Spatie\EventProjector\Exceptions;

use Exception;
use Spatie\EventProjector\Models\StoredEvent;

final class InvalidStoredEvent extends Exception
{
    public static function couldNotUnserializeEvent(StoredEvent $storedEvent, Exception $serializerException): self
    {
        return new static("Failed to unserialize an event with class `{$storedEvent->event_class}` on stored event with id `{$storedEvent->id}`. Are you sure that event class exists? ", 0, $serializerException);
    }
}
