<?php

namespace Spatie\EventSourcing\StoredEvents;

use AllowDynamicProperties;
use Carbon\CarbonImmutable;
use ReflectionClass;
use Spatie\EventSourcing\Attributes\EventVersion;
use Spatie\EventSourcing\Enums\MetaData;
use Symfony\Component\Serializer\Attribute\Ignore;

#[AllowDynamicProperties]
abstract class ShouldBeStored
{
    protected array $metaData = [];

    #[Ignore]
    public function eventVersion(): int
    {
        $versionAttribute = (new ReflectionClass($this))->getAttributes(EventVersion::class)[0] ?? null;

        if (! $versionAttribute) {
            return 1;
        }

        return $versionAttribute->newInstance()->version;
    }

    #[Ignore]
    public function createdAt(): ?CarbonImmutable
    {
        return CarbonImmutable::make($this->metaData[MetaData::CREATED_AT] ?? null);
    }

    #[Ignore]
    public function setCreatedAt(CarbonImmutable $createdAt): self
    {
        $this->metaData[MetaData::CREATED_AT] = $createdAt;

        return $this;
    }

    #[Ignore]
    public function aggregateRootUuid(): ?string
    {
        return $this->metaData[MetaData::AGGREGATE_ROOT_UUID] ?? null;
    }

    #[Ignore]
    public function setAggregateRootUuid(string $uuid): self
    {
        $this->metaData[MetaData::AGGREGATE_ROOT_UUID] = $uuid;

        return $this;
    }

    #[Ignore]
    public function storedEventId(): int | string | null
    {
        return $this->metaData[MetaData::STORED_EVENT_ID] ?? null;
    }

    #[Ignore]
    public function setStoredEventId(int | string $id): self
    {
        $this->metaData[MetaData::STORED_EVENT_ID] = $id;

        return $this;
    }

    #[Ignore]
    public function aggregateRootVersion(): ?int
    {
        return $this->metaData[MetaData::AGGREGATE_ROOT_VERSION] ?? null;
    }

    #[Ignore]
    public function setAggregateRootVersion(int $version): self
    {
        $this->metaData[MetaData::AGGREGATE_ROOT_VERSION] = $version;

        return $this;
    }

    #[Ignore]
    public function metaData(): array
    {
        return $this->metaData;
    }

    #[Ignore]
    public function setMetaData(array $metaData): self
    {
        $this->metaData = $metaData;

        return $this;
    }
}
