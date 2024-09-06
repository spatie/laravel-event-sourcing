<?php

namespace Spatie\EventSourcing\StoredEvents\Exceptions;

use Exception;

class EventClassMapMissing extends Exception
{
    public static function noEventClassMappingProvided(string $eventClass): self
    {
        return new static(
            "The alias for $eventClass is missing. Add it to the event_class_map config or disable the enforce_event_class_map option.",
        );
    }
}
