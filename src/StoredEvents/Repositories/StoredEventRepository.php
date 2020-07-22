<?php

namespace Spatie\EventSourcing\StoredEvents\Repositories;

use Illuminate\Support\LazyCollection;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

interface StoredEventRepository
{
    public function retrieveAll(string $uuid = null): LazyCollection;

    public function retrieveAllStartingFrom(int $startingFrom, string $uuid = null): LazyCollection;

    public function retrieveAllAfterVersion(int $aggregateVersion, string $aggregateUuid): LazyCollection;

    public function countAllStartingFrom(int $startingFrom, string $uuid = null): int;

    public function persist(ShouldBeStored $event, string $uuid = null, int $aggregateVersion = null): StoredEvent;

    public function persistMany(array $events, string $uuid = null, int $aggregateVersion = null): LazyCollection;

    public function update(StoredEvent $storedEvent): StoredEvent;

    public function getLatestAggregateVersion(string $aggregateUuid): int;
}
