<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;

interface StoredEventRepository
{
    public static function retrieveAll(string $uuid = null, int $startingFrom = null): Collection;

    public static function persist(ShouldBeStored $event, string $uuid = null, string $model = null): StoredEvent;

    public static function persistMany(array $events, string $uuid = null, string $model = null): Collection;

    public static function update(StoredEvent $storedEventData): StoredEvent;
}
