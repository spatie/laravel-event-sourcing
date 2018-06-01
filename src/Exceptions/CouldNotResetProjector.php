<?php

namespace Spatie\EventProjector\Exceptions;

use Exception;
use Spatie\EventProjector\Projectors\Projector;

class CouldNotResetProjector extends Exception
{
    public static function doesNotHaveResetStateMethod(Projector $projector)
    {
        return new static("Could not reset the projector named `{$projector->getName()}` because it does not have a `resetState` method.");
    }
}
