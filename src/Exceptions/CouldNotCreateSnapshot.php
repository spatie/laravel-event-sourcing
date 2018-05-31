<?php

namespace Spatie\EventProjector\Exceptions;

use Exception;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Snapshots\Snapshot;

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

    public static function newEventsStoredDuringSnapshotCreation(Snapshot $snapshot, Collection $newEventsHandledByProjectorOfSnapshot)
    {
        return new static("While creating a snapshot for projector `{$snapshot->name()}` new events were stored where the projector listens for. The validity of the snapshot could not be guarantied so it was deleted. Try creating the snapshot again.");
    }
}
