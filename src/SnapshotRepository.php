<?php

namespace Spatie\EventSourcing;

interface SnapshotRepository
{
    public function retrieve(string $aggregateUuid): ?Snapshot;

    public function persist(Snapshot $snapshot): Snapshot;
}
