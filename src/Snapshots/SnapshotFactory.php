<?php

namespace Spatie\EventProjector\Snapshots;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Exceptions\CouldNotCreateSnapshot;

class SnapshotFactory
{
    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    /** @var \Spatie\EventProjector\Snapshots\Filesystem */
    protected $disk;

    public function __construct(EventProjectionist $eventProjectionist, Filesystem $disk)
    {
        $this->eventProjectionist = $eventProjectionist;

        $this->disk = $disk;
    }

    public function createForProjector(Snapshottable $projector, string $name = ''): Snapshot
    {
        $lastEventId = $projector->getLastProcessedEventId();

        $projectorName = str_replace('\\', '+', $projector->getName());

        $fileName = "Snapshot---{$projectorName}---{$lastEventId}---{$name}.txt";

        $snapshot = new Snapshot(
            $this->eventProjectionist,
            $this->disk,
            $fileName
        );

        try {
            $projector->writeToSnapshot($snapshot);
        } catch (Exception $exception) {
            try {
                $snapshot->delete();
            } catch (Exception $exception) {
            }

            throw CouldNotCreateSnapshot::projectorThrewExceptionDuringWritingToSnapshot($projector, $exception);
        }

        if (!$this->disk->exists($fileName)) {
            throw CouldNotCreateSnapshot::projectorDidNotWriteAnythingToSnapshot($projector);
        }

        if (!static::snapshotIsValid($lastEventId)) {
            $snapshot->delete();
        }

        return $snapshot;
    }

    public function createForFile(Filesystem $disk, string $fileName): Snapshot
    {
        return new Snapshot($this->eventProjectionist, $disk, $fileName);
    }

    protected static function snapshotIsValid(int $lastEventId): bool
    {
        return true;
    }
}