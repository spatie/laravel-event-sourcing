<?php

namespace Spatie\EventProjector\Exceptions;

use Exception;
use Spatie\EventProjector\ShouldBeStored;

class InvalidEventHandler extends Exception
{
    public static function eventHandlingMethodDoesNotExist(object $eventHandler, ShouldBeStored $event, string $methodName)
    {
        $eventHandlerClass = get_class($eventHandler);
        $eventClass = get_class($event);

        return new static("Tried to call `$methodName` on `$eventHandlerClass` to handle an event of class `$eventClass` but that method does not exist.");
    }

    public static function doesNotExist(string $eventHandlerClass)
    {
        return new static("The event handler class `{$eventHandlerClass}` does not exist.");
    }
}
