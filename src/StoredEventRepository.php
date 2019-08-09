<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEventData;

interface StoredEventRepository
{
    public static function retrieveAll(string $uuid = null, int $startingFrom = null): Collection;

    public static function persist(ShouldBeStored $event, string $uuid = null): StoredEventData;

    public static function persistMany(array $events, string $uuid = null): Collection;

    public static function update(StoredEventData $storedEventData): StoredEventData;
}
