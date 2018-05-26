<?php

namespace Spatie\EventProjector\Snapshots;

trait CanTakeSnapshot
{
    public function takeSnapshot(string $name = ''): Snapshot
    {
        return Snapshot::createForProjector($this, $name);
    }
}