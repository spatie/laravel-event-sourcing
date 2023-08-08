<?php

namespace Spatie\EventSourcing\Exceptions;

use Exception;

class InvalidStorableEvent extends Exception
{
    public static function notAStorableEventClassName(string $className): self
    {
        return new static('`'.$className.'` must implement Spatie\EventSourcing\StoredEvents\ShouldBeStored');
    }
}
