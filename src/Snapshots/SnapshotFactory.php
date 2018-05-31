<?php

namespace Spatie\EventProjector\Snapshots;

use Exception;
use Spatie\EventProjector\EventProjectionist;
use Illuminate\Contracts\Filesystem\Filesystem;
use Spatie\EventProjector\Exceptions\CouldNotCreateSnapshot;
use Spatie\EventProjector\Models\StoredEvent;

class SnapshotFactory
{
    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    /** @var \Spatie\EventProjector\Snapshots\Filesystem */
    protected $disk;

    /** @var array */
    protected $config;

    public function __construct(EventProjectionist $eventProjectionist, Filesystem $disk, array $config)
    {
        $this->eventProjectionist = $eventProjectionist;

        $this->disk = $disk;

        $this->config = $config;
    }

    public function createForProjector(Snapshottable $projector, string $name = ''): Snapshot
    {
        $lastEventId = $projector->getLastProcessedEventId();

        $projectorName = str_replace('\\', '+', $projector->getName());

        $fileName = "Snapshot---{$projectorName}---{$lastEventId}---{$name}.txt";

        $snapshot = new Snapshot(
            $this->eventProjectionist,
            $this->config,
            $this->disk,
            $fileName
        );

        try {
            $projector->writeToSnapshot($snapshot);
        } catch (Exception $exception) {
            $this->attemptToDeleteSnapshot($snapshot);

            throw CouldNotCreateSnapshot::projectorThrewExceptionDuringWritingToSnapshot($projector, $exception);
        }

        if (!$this->disk->exists($fileName)) {
            throw CouldNotCreateSnapshot::projectorDidNotWriteAnythingToSnapshot($projector);
        }

        $this->guardAgainstInvalidSnapshot($snapshot);

        return $snapshot;
    }

    public function createForFile(Filesystem $disk, string $fileName): Snapshot
    {
        return new Snapshot(
            $this->eventProjectionist,
            $this->config,
            $disk,
            $fileName);
    }

    protected function guardAgainstInvalidSnapshot(Snapshot $snapshot)
    {
        $newStoredEvents = $this->config['stored_event_model']::after($snapshot->lastProcessedEventId())->get();

        $newEventsHandledByProjectorOfSnapshot = $newStoredEvents
            ->filter(function (StoredEvent $storedEvent) use ($snapshot) {
                return $snapshot->projector()->handlesEvent($storedEvent->event);
        });

        if (! $newEventsHandledByProjectorOfSnapshot->isEmpty())
        {
            $this->attemptToDeleteSnapshot($snapshot);

            throw CouldNotCreateSnapshot::newEventsStoredDuringSnapshotCreation($snapshot, $newEventsHandledByProjectorOfSnapshot);
        }
    }

    protected function attemptToDeleteSnapshot(Snapshot $snapshot)
    {
        try {
            $snapshot->delete();
        } catch (Exception $exception) {
        }
    }
}
