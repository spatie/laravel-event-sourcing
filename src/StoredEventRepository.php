<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEventData;

interface StoredEventRepository
{
    public function retrieveAll(string $uuid = null, int $startingFrom = null): Collection;
    public function persist(ShouldBeStored $event, string $uuid = null): StoredEventData;
    public function update(StoredEventData $storedEventData): StoredEventData;
}
