<?php

namespace Spatie\EventSourcing\Tests;

use PHPUnit\Framework\Assert;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;

use Spatie\EventSourcing\StoredEvents\Repositories\EloquentStoredEventRepository;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithCustomSerializer;

it('can get the latest version id for a given aggregate uuid', function () {
    $eloquentStoredEventRepository = new EloquentStoredEventRepository();

    assertEquals(0, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-non-existing'));

    $aggregateRoot = AccountAggregateRoot::retrieve('uuid-1');
    assertEquals(0, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-1'));

    $aggregateRoot->addMoney(100)->persist();
    assertEquals(1, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-1'));

    $aggregateRoot->addMoney(100)->persist();
    assertEquals(2, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-1'));

    $anotherAggregateRoot = AccountAggregateRoot::retrieve('uuid-2');
    $anotherAggregateRoot->addMoney(100)->persist();
    assertEquals(1, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-2'));
    assertEquals(2, $eloquentStoredEventRepository->getLatestAggregateVersion('uuid-1'));
});

it('sets the original event on persist', function () {
    $eloquentStoredEventRepository = app(EloquentStoredEventRepository::class);

    $originalEvent = new MoneyAdded(100);
    $storedEvent = $eloquentStoredEventRepository->persist($originalEvent, 'uuid-1', 1);

    assertSame($originalEvent, $storedEvent->event);
});

it('uses the custom serializer if one is set', function () {
    $eloquentStoredEventRepository = app(EloquentStoredEventRepository::class);

    $originalEvent = new EventWithCustomSerializer('default message');
    $storedEvent = $eloquentStoredEventRepository->persist($originalEvent, 'uuid-1', 1);

    $eventFromDatabase = $eloquentStoredEventRepository->find($storedEvent->id)->event;
    assertSame('message set by custom serializer', $eventFromDatabase->message);
});

it('uses id ordering by default when retrieving aggregate events', function () {
    config()->set('event-sourcing.aggregate_event_order_column', 'id');

    $aggregateRoot = AccountAggregateRoot::retrieve('uuid-order-test');
    $aggregateRoot->addMoney(100)->addMoney(200)->addMoney(300)->persist();

    $repository = new EloquentStoredEventRepository();
    $events = $repository->retrieveAll('uuid-order-test');

    $eventArray = $events->all();
    Assert::assertCount(3, $eventArray);

    // Events should be ordered by id (which is the default)
    assertEquals(100, $eventArray[0]->event->amount);
    assertEquals(200, $eventArray[1]->event->amount);
    assertEquals(300, $eventArray[2]->event->amount);
});

it('uses aggregate_version ordering when configured', function () {
    config()->set('event-sourcing.aggregate_event_order_column', 'aggregate_version');

    $aggregateRoot = AccountAggregateRoot::retrieve('uuid-version-test');
    $aggregateRoot->addMoney(100)->addMoney(200)->addMoney(300)->persist();

    $repository = new EloquentStoredEventRepository();
    $events = $repository->retrieveAll('uuid-version-test');

    $eventArray = $events->all();
    Assert::assertCount(3, $eventArray);

    // Events should be ordered by aggregate_version
    assertEquals(100, $eventArray[0]->event->amount);
    assertEquals(200, $eventArray[1]->event->amount);
    assertEquals(300, $eventArray[2]->event->amount);

    // Verify they are ordered by aggregate_version
    assertEquals(1, $eventArray[0]->aggregate_version);
    assertEquals(2, $eventArray[1]->aggregate_version);
    assertEquals(3, $eventArray[2]->aggregate_version);
});

it('always uses id ordering for global queries without uuid', function () {
    config()->set('event-sourcing.aggregate_event_order_column', 'aggregate_version');

    // Create events for multiple aggregates
    AccountAggregateRoot::retrieve('uuid-global-1')->addMoney(100)->persist();
    AccountAggregateRoot::retrieve('uuid-global-2')->addMoney(200)->persist();
    AccountAggregateRoot::retrieve('uuid-global-3')->addMoney(300)->persist();

    $repository = new EloquentStoredEventRepository();
    $events = $repository->retrieveAll(); // No UUID - global query

    $eventArray = $events->all();

    // Should be ordered by id (global ordering) regardless of config
    // We can verify this by checking the ids are sequential
    for ($i = 1; $i < count($eventArray); $i++) {
        $this->assertGreaterThan($eventArray[$i - 1]->id, $eventArray[$i]->id);
    }
});

it('uses configured ordering in retrieveAllAfterVersion', function () {
    config()->set('event-sourcing.aggregate_event_order_column', 'aggregate_version');

    $aggregateRoot = AccountAggregateRoot::retrieve('uuid-after-version');
    $aggregateRoot->addMoney(100)->addMoney(200)->persist();
    $aggregateRoot->snapshot();
    $aggregateRoot->addMoney(300)->addMoney(400)->persist();

    $repository = new EloquentStoredEventRepository();
    $events = $repository->retrieveAllAfterVersion(2, 'uuid-after-version');

    $eventArray = $events->all();
    Assert::assertCount(2, $eventArray);

    // Should be ordered by aggregate_version
    assertEquals(300, $eventArray[0]->event->amount);
    assertEquals(400, $eventArray[1]->event->amount);
    assertEquals(3, $eventArray[0]->aggregate_version);
    assertEquals(4, $eventArray[1]->aggregate_version);
});

it('uses configured ordering in retrieveAllStartingFrom with uuid', function () {
    config()->set('event-sourcing.aggregate_event_order_column', 'aggregate_version');

    $aggregateRoot = AccountAggregateRoot::retrieve('uuid-starting-from');
    $aggregateRoot->addMoney(100)->addMoney(200)->addMoney(300)->persist();

    $repository = new EloquentStoredEventRepository();
    $firstEvent = $repository->retrieveAll('uuid-starting-from')->first();

    $events = $repository->retrieveAllStartingFrom($firstEvent->id + 1, 'uuid-starting-from');

    $eventArray = $events->all();
    Assert::assertCount(2, $eventArray);

    // Should be ordered by aggregate_version
    assertEquals(200, $eventArray[0]->event->amount);
    assertEquals(300, $eventArray[1]->event->amount);
});

it('uses id ordering in retrieveAllStartingFrom without uuid', function () {
    config()->set('event-sourcing.aggregate_event_order_column', 'aggregate_version');

    AccountAggregateRoot::retrieve('uuid-starting-1')->addMoney(100)->persist();
    AccountAggregateRoot::retrieve('uuid-starting-2')->addMoney(200)->persist();

    $repository = new EloquentStoredEventRepository();
    $firstEvent = $repository->retrieveAll()->first();

    $events = $repository->retrieveAllStartingFrom($firstEvent->id + 1);

    $eventArray = $events->toArray();

    // Should be ordered by id (global ordering)
    for ($i = 1; $i < count($eventArray); $i++) {
        $this->assertGreaterThan($eventArray[$i - 1]->id, $eventArray[$i]->id);
    }
});

it('aggregate reconstitution works correctly with aggregate_version ordering', function () {
    config()->set('event-sourcing.aggregate_event_order_column', 'aggregate_version');

    $aggregateRoot = AccountAggregateRoot::retrieve('uuid-reconstitute');
    $aggregateRoot
        ->addMoney(100)
        ->addMoney(200)
        ->addMoney(300)
        ->persist();

    // Retrieve the aggregate again - this should reconstitute from events
    $reconstitutedAggregate = AccountAggregateRoot::retrieve('uuid-reconstitute');

    assertEquals(600, $reconstitutedAggregate->balance);
    assertEquals(3, $reconstitutedAggregate->aggregateVersion);
});

it('aggregate reconstitution with snapshot works correctly with aggregate_version ordering', function () {
    config()->set('event-sourcing.aggregate_event_order_column', 'aggregate_version');

    $aggregateRoot = AccountAggregateRoot::retrieve('uuid-snapshot-reconstitute');
    $aggregateRoot->addMoney(100)->addMoney(200)->persist();
    $aggregateRoot->snapshot();
    $aggregateRoot->addMoney(300)->persist();

    // Retrieve the aggregate again - should use snapshot + events after
    $reconstitutedAggregate = AccountAggregateRoot::retrieve('uuid-snapshot-reconstitute');

    assertEquals(600, $reconstitutedAggregate->balance);
    assertEquals(3, $reconstitutedAggregate->aggregateVersion);
});
