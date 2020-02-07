<?php

namespace Spatie\EventSourcing\Snapshots;

interface SnapshotRepository
{
    public function retrieve(string $aggregateUuid): ?Snapshot;

    public function persist(Snapshot $snapshot): Snapshot;
}
