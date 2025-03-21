<?php

namespace Spatie\EventSourcing\Tests;

use Carbon\CarbonImmutable;

use function PHPUnit\Framework\assertEquals;

use Spatie\EventSourcing\Enums\MetaData;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\StoredEvents\Repositories\EloquentStoredEventRepository;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;

it('correctly handles created_at in meta data', function () {
    $now = CarbonImmutable::now()->setTimezone('Asia/Singapore');
    $customCreatedAt = $now;
    $event = new MoneyAdded(100);
    $event->setMetaData([
        MetaData::CREATED_AT => $customCreatedAt,
    ]);

    $repository = app(EloquentStoredEventRepository::class);
    $storedEvent = $repository->persist($event, 'test-uuid');

    $eloquentStoredEvent = EloquentStoredEvent::query()->find($storedEvent->id);
    $shouldBeStoredEvent = $eloquentStoredEvent->toStoredEvent()->event;
    assertEquals($shouldBeStoredEvent->createdAt()->toDateTimeString(), $customCreatedAt->toDateTimeString());
});
