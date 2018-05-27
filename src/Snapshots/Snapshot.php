<?php

namespace Spatie\EventProjector\Snapshots;

use Spatie\EventProjector\EventProjectionist;
use Illuminate\Contracts\Filesystem\Filesystem;
use Spatie\EventProjector\Projectors\Projector;

class Snapshot
{
    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var string */
    protected $fileName;

    public function __construct(EventProjectionist $eventProjectionist, Filesystem $disk, string $fileName)
    {
        $this->eventProjectionist = $eventProjectionist;

        $this->disk = $disk;

        $this->fileName = $fileName;
    }

    public function getLastProcessedEventId(): int
    {
        return $this->getNameParts()['lastProcessedEventId'];
    }

    public function getProjectorName(): string
    {
        return $this->getNameParts()['projectorName'];
    }

    public function getProjector(): Projector
    {
        $projectorName = $this->getProjectorName();

        return $this->eventProjectionist->getProjector($projectorName);
    }

    public function getName(): string
    {
        return $this->getNameParts()['name'];
    }

    /**
     * @param string|resource $contents
     */
    public function write($contents)
    {
        $this->disk->put($this->fileName, $contents);
    }

    public function read(): ?string
    {
        return $this->disk->get($this->fileName);
    }

    public function readStream()
    {
        return $this->disk->readStream($this->fileName);
    }

    protected function getNameParts(): array
    {
        $baseName = pathinfo($this->fileName, PATHINFO_FILENAME);

        $nameParts = explode('---', $baseName);

        $projectorName =  $nameParts[1];

        $lastProcessedEventId = (int) $nameParts[2];

        $name =  $nameParts[3] ?? '';

        return compact('projectorName', 'lastProcessedEventId', 'name');
    }

    public function delete()
    {
        $this->disk->delete($this->fileName);
    }
}