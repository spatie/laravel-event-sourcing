<?php

namespace Spatie\EventProjector\Exceptions;

use Exception;
use Spatie\EventProjector\Snapshots\Snapshot;

class InvalidSnapshot extends Exception
{
    public static function lastProcessedEventDoesNotExist(Snapshot $snapshot)
    {
        return new static("Snapshot {$snapshot->fileName()} was taken for a non-existing stored event with id `{$snapshot->lastProcessedEventId()}`. Make sure a stored event with that id exists.");
    }

    public static function projectorDoesNotExist(Snapshot $snapshot)
    {
        return new static("Snapshot `{$snapshot->fileName()}` was taken for a projector named `{$snapshot->projectorName()}` but no such projector was found. Make sure you add all projectors to the Projectionist.");
    }
}
