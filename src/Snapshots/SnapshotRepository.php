<?php

namespace Spatie\EventSourcing\Snapshots;

interface SnapshotRepository
{
    public function retrieve(string $aggregateUuid, int $maxAggregateVersion = null): ?Snapshot;

    public function persist(Snapshot $snapshot): Snapshot;
}
