<?php

namespace Spatie\EventProjector\Exceptions;

use Exception;
use Spatie\EventProjector\Projectors\Projector;

class CouldNotCreateSnapshot extends Exception
{
    public static function projectorDidNotWriteAnythingToSnapshot(Projector $projector)
    {
        return new static("The projector named {$projector->getName()} didn't write to the snapshot. When snapshotting a projector should write it's state to the snapshot.");
    }

    public static function projectorThrewExceptionDuringWritingToSnapshot(Projector $projector, Exception $exception)
    {
        return new static("The projector named {$projector->getName()} threw an exception while writing to a snapshot", 0, $exception);
    }
}
