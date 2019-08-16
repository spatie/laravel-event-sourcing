<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;

interface StoredEventRepository
{
    public function retrieveAll(string $uuid = null, int $startingFrom = null): Collection;

    public function persist(ShouldBeStored $event, string $uuid = null): StoredEvent;

    public function persistMany(array $events, string $uuid = null): Collection;

    public function update(StoredEvent $storedEvent): StoredEvent;
}
