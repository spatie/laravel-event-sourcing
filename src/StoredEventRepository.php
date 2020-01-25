<?php

namespace Spatie\EventSourcing;

use Illuminate\Support\LazyCollection;

interface StoredEventRepository
{
    public function retrieveAll(string $uuid = null): LazyCollection;

    public function retrieveAllStartingFrom(int $startingFrom, string $uuid = null): LazyCollection;

    public function retrieveAllAfterVersion(int $aggregateVersion, string $aggregateUuid): LazyCollection;

    public function persist(ShouldBeStored $event, string $uuid = null, int $aggregateVersion = null): StoredEvent;

    public function persistMany(array $events, string $uuid = null, int $aggregateVersion = null): LazyCollection;

    public function update(StoredEvent $storedEvent): StoredEvent;

    public function getLatestVersion(string $aggregateUuid): int;
}
