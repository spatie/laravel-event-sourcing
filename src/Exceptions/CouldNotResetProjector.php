<?php

namespace Spatie\EventProjector\Exceptions;

use Exception;
use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\ShouldBeStored;

class CouldNotResetProjector extends Exception
{
    public static function doesNotHaveResetStateMethod(Projector $projector)
    {
        return new static("Could not reset the projector named `{$projector->getName()}` because it does not have a `resetState` method.");
    }
}
