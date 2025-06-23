<?php

namespace Spatie\EventSourcing\StoredEvents\Repositories;

use Illuminate\Support\LazyCollection;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

interface StoredEventRepository
{
    public function find(int $id): StoredEvent;

    public function retrieveAll(?string $uuid = null): LazyCollection;

    public function retrieveAllStartingFrom(int $startingFrom, ?string $uuid = null, array $events = []): LazyCollection;

    public function runForAllStartingFrom(int $startingFrom, callable|\Closure $function, int $chunkSize = 1000, ?string $uuid = null, array $events = []): bool;

    public function retrieveAllAfterVersion(int $aggregateVersion, string $aggregateUuid): LazyCollection;

    public function countAllStartingFrom(int $startingFrom, ?string $uuid = null, array $events = []): int;

    public function persist(ShouldBeStored $event, ?string $uuid = null): StoredEvent;

    public function persistMany(array $events, ?string $uuid = null): LazyCollection;

    public function update(StoredEvent $storedEvent): StoredEvent;

    public function getLatestAggregateVersion(string $aggregateUuid): int;
}
