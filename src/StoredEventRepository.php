<?php

namespace Spatie\EventProjector;

use Illuminate\Support\LazyCollection;
use Spatie\EventProjector\Models\StoredEvent;

interface StoredEventRepository
{
    public function retrieveAll(string $uuid = null): LazyCollection;

    public function retrieveAllStartingFrom(int $startingFrom, string $uuid = null): LazyCollection;

    public function persist(ShouldBeStored $event, string $uuid = null): StoredEvent;

    public function persistMany(array $events, string $uuid = null): LazyCollection;

    public function update(StoredEvent $storedEvent): StoredEvent;
}
