<?php

namespace Spatie\EventProjector\Snapshots;

use Carbon\Carbon;
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

    public function isValid(): bool
    {
        return $this->projectorName() !== '';
    }

    public function lastProcessedEventId(): int
    {
        return $this->getNameParts()['lastProcessedEventId'];
    }

    public function projectorName(): string
    {
        return $this->getNameParts()['projectorName'];
    }

    public function getProjector(): Projector
    {
        $projectorName = $this->projectorName();

        return $this->eventProjectionist->getProjector($projectorName);
    }

    public function name(): string
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

    public function delete()
    {
        $this->disk->delete($this->fileName);
    }

    public function createdAt(): Carbon
    {
        $timestamp = $this->disk->lastModified($this->fileName);

        return Carbon::createFromTimestamp($timestamp);
    }

    protected function getNameParts(): array
    {
        $baseName = pathinfo($this->fileName, PATHINFO_FILENAME);

        $nameParts = explode('---', $baseName);

        $projectorName =  str_replace('+', '\\', $nameParts[1] ?? '');

        $lastProcessedEventId = $nameParts[2] ?? 0;

        $name =  $nameParts[3] ?? '';

        return compact('projectorName', 'lastProcessedEventId', 'name');
    }
}