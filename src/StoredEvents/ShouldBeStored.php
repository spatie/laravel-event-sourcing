<?php

namespace Spatie\EventSourcing\StoredEvents;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use Spatie\EventSourcing\Attributes\EventAlias;
use Spatie\EventSourcing\Attributes\EventVersion;
use Spatie\EventSourcing\Enums\MetaData;

abstract class ShouldBeStored
{
    protected array $metaData = [];

    public function eventVersion(): int
    {
        return static::attribute(EventVersion::class)?->version ?? 1;
    }

    public static function eventName(string $mappedAlias = null): string
    {
        return $mappedAlias
            ?? static::attribute(EventAlias::class)?->alias
            ?? Str::kebab(class_basename(static::class));
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

    protected static function attributes(string $attribute, bool $ascend = false): Collection
    {
        $classRef = new ReflectionClass(static::class);
        $attrRefs = collect();

        do {
            $attrRefs->push(...$classRef->getAttributes($attribute));
        } while ($ascend && false !== $classRef = $classRef->getParentClass());

        return $attrRefs->map->newInstance();
    }

    protected static function attribute(string $attribute, bool $ascend = false)
    {
        return self::attributes($attribute, $ascend)->first();
    }
}
