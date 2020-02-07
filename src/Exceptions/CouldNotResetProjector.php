<?php

namespace Spatie\EventSourcing\Exceptions;

use Exception;
use Spatie\EventSourcing\Projectors\Projector;

class CouldNotResetProjector extends Exception
{
    public static function doesNotHaveResetStateMethod(Projector $projector): self
    {
        return new static("Could not reset the projector named `{$projector->getName()}` because it does not have a `resetState` method.");
    }
}
