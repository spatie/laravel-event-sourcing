<?php

namespace Spatie\EventSourcing\StoredEvents;

use Spatie\EventSourcing\Enums\MetaData;

abstract class ShouldBeStored
{
    private array $metaData = [];

    public function aggregateRootUuid(): ?string
    {
        return $this->metaData[MetaData::AGGREGATE_ROOT_UUID] ?? null;
    }

    public function setAggregateRootUuid(string $uuid): self
    {
        $this->metaData[MetaData::AGGREGATE_ROOT_UUID] = $uuid;

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
