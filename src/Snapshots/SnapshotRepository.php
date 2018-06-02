<?php

namespace Spatie\EventProjector\Snapshots;

use Illuminate\Support\Collection;
use Spatie\EventProjector\EventProjectionist;
use Illuminate\Contracts\Filesystem\Filesystem;

class SnapshotRepository
{
    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var array */
    protected $config;

    public function __construct(EventProjectionist $eventProjectionist, Filesystem $disk,  array $config)
    {
        $this->eventProjectionist = $eventProjectionist;

        $this->disk = $disk;

        $this->config = $config;
    }

    public function get(): Collection
    {
        return collect($this->disk->allFiles())
            ->map(function (string $fileName) {
                return new Snapshot($this->eventProjectionist, $this->config, $this->disk, $fileName);
            })
            ->filter->isValid()
            ->sortByDesc(function (Snapshot $snapshot) {
                return $snapshot->createdAt()->format('timestamp');
            })
            ->values();
    }
}
