<?php

namespace Spatie\EventProjector\Snapshots;

use Spatie\EventProjector\Projectors\Projector;

interface Snapshottable extends Projector
{
    public function writeToSnapshot(Snapshot $snapshot);

    public function restoreFromSnapshot(Snapshot $snapshot);
}
