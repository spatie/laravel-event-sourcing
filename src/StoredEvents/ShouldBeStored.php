<?php

namespace Spatie\EventSourcing\StoredEvents;

use AllowDynamicProperties;
use Carbon\CarbonImmutable;
use ReflectionClass;
use Spatie\EventSourcing\Attributes\EventVersion;
use Spatie\EventSourcing\Enums\MetaData;

#[AllowDynamicProperties]
abstract class ShouldBeStored
{
    protected array $metaData = [];

    public function eventVersion(): int
    {
        $versionAttribute = (new ReflectionClass($this))->getAttributes(EventVersion::class)[0] ?? null;

        if (! $versionAttribute) {
            return 1;
        }

        return $versionAttribute->newInstance()->version;
    }

    public function createdAt(): ?CarbonImmutable
    {
        return CarbonImmutable::make($this->metaData[MetaData::CREATED_AT] ?? null);
    }

    public function setCreatedAt(CarbonImmutable $createdAt): self
    {
        $this->metaData[MetaData::CREATED_AT] = $createdAt;

        return $this;
    }

    public function aggregateRootUuid(): ?string
    {
        return $this->metaData[MetaData::AGGREGATE_ROOT_UUID] ?? null;
    }

    public function setAggregateRootUuid(string $uuid): self
    {
        $this->metaData[MetaData::AGGREGATE_ROOT_UUID] = $uuid;

        return $this;
    }

    public function storedEventId(): ?int
    {
        return $this->metaData[MetaData::STORED_EVENT_ID] ?? null;
    }

    public function setStoredEventId(int $id): self
    {
        $this->metaData[MetaData::STORED_EVENT_ID] = $id;

        return $this;
    }

    public function aggregateRootVersion(): ?int
    {
        return $this->metaData[MetaData::AGGREGATE_ROOT_VERSION] ?? null;
    }

    public function setAggregateRootVersion(int $version): self
    {
        $this->metaData[MetaData::AGGREGATE_ROOT_VERSION] = $version;

        return $this;
    }

    public function metaData(): array
    {
        return $this->metaData;
    }

    public function setMetaData(array $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }
}
