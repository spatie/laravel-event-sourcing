<?php

namespace Spatie\EventSourcing\Snapshots;

use Spatie\EventSourcing\Snapshots\Snapshot;

interface SnapshotRepository
{
    public function retrieve(string $aggregateUuid): ?Snapshot;

    public function persist(Snapshot $snapshot): Snapshot;
}
