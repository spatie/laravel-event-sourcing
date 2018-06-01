<?php

namespace Spatie\EventProjector\Snapshots;

use Carbon\Carbon;
use Spatie\EventProjector\EventProjectionist;
use Spatie\EventProjector\Models\StoredEvent;
use Illuminate\Contracts\Filesystem\Filesystem;
use Spatie\EventProjector\Exceptions\InvalidSnapshot;

class Snapshot
{
    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    /** @var array */
    protected $config;

    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    /** @var string */
    protected $fileName;

    public function __construct(
        EventProjectionist $eventProjectionist,
        array $config,
        Filesystem $disk,
        string $fileName)
    {
        $this->eventProjectionist = $eventProjectionist;
        $this->config = $config;
        $this->disk = $disk;
        $this->fileName = $fileName;
    }

    public function isValid(): bool
    {
        return $this->projectorName() !== '';
    }

    public function fileName(): string
    {
        return $this->fileName;
    }

    public function lastProcessedEventId(): int
    {
        return $this->fileNameParts()['lastProcessedEventId'];
    }

    public function lastProcessedEvent(): StoredEvent
    {
        $storedEventModelClass = $this->config['stored_event_model'];

        $storedEvent = $storedEventModelClass::query()
            ->where('id', $this->lastProcessedEventId())
            ->first();

        if (! $storedEvent) {
            throw InvalidSnapshot::lastProcessedEventDoesNotExist($this);
        }

        return $storedEvent;
    }

    public function projectorName(): string
    {
        return $this->fileNameParts()['projectorName'];
    }

    public function projector(): Snapshottable
    {
        $projectorName = $this->projectorName();

        if (! $projector = $this->eventProjectionist->getProjector($projectorName)) {
            throw InvalidSnapshot::projectorDoesNotExist($this);
        }

        return $projector;
    }

    public function name(): string
    {
        return $this->fileNameParts()['name'];
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

    public function restore()
    {
        $storedEvent = $this->lastProcessedEvent();

        $this->projector()->restoreSnapshot($this);

        $projectorStatus = $this->config['projector_status_model']::getForProjector($this->projector());

        $projectorStatus->rememberLastProcessedEvent($storedEvent);
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

    protected function fileNameParts(): array
    {
        $baseName = pathinfo($this->fileName, PATHINFO_FILENAME);

        $nameParts = explode('---', $baseName);

        $projectorName = str_replace('+', '\\', $nameParts[1] ?? '');

        $lastProcessedEventId = $nameParts[2] ?? 0;

        $name = $nameParts[3] ?? '';

        return compact('projectorName', 'lastProcessedEventId', 'name');
    }
}
