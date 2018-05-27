<?php

namespace Spatie\EventProjector\Snapshots;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Spatie\EventProjector\EventProjectionist;

class SnapshotRepository
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    public function __construct(Filesystem $disk, EventProjectionist $eventProjectionist)
    {
        $this->disk = $disk;

        $this->eventProjectionist = $eventProjectionist;
    }

    public function all(): Collection
    {
        return collect($this->disk->allFiles())->map(function(string $fileName) {
            return new Snapshot($this->eventProjectionist, $this->disk, $fileName);
        });
    }
}