<?php

namespace Spatie\EventSourcing\Tests;

use Exception;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\AggregateRoots\Exceptions\CouldNotPersistAggregate;
use Spatie\EventSourcing\AggregateRoots\Exceptions\InvalidEloquentStoredEventModel;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\Snapshots\EloquentSnapshot;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRootWithFailingPersist;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRootWithStoredEventRepositorySpecified;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Mailable\MoneyAddedMailable;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Math;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors\AccountProjector;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Reactors\SendMailReactor;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyMultiplied;
use Spatie\EventSourcing\Tests\TestClasses\FakeUuid;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Models\InvalidEloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\OtherEloquentStoredEvent;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    $this->aggregateUuid = FakeUuid::generate();
});

test('aggregate root resolves dependencies from the container', function () {
    $this->app->bind(AccountAggregateRoot::class, function () {
        return new AccountAggregateRoot(app(Math::class), 42);
    });

    $root = AccountAggregateRoot::retrieve($this->aggregateUuid);

    assertEquals(42, $root->dependency);
});

test('it can get the uuid', function () {
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    assertEquals($this->aggregateUuid, $aggregateRoot->uuid());
});

test('persisting an aggregate root will persist all events it recorded', function () {
    AccountAggregateRoot::retrieve($this->aggregateUuid)
        ->addMoney(100)
        ->persist();

    $storedEvents = EloquentStoredEvent::get();
    assertCount(1, $storedEvents);

    $storedEvent = $storedEvents->first();
    assertEquals($this->aggregateUuid, $storedEvent->aggregate_uuid);

    $event = $storedEvent->event;
    assertInstanceOf(MoneyAdded::class, $event);
    assertEquals(100, $event->amount);
});

test('when an aggregate root specifies a stored event repository persisting will persist all events it recorded via repository', function () {
    AccountAggregateRootWithStoredEventRepositorySpecified::retrieve($this->aggregateUuid)
        ->addMoney(100)
        ->persist();

    $storedEvents = EloquentStoredEvent::get();
    assertCount(0, $storedEvents);

    $otherStoredEvents = OtherEloquentStoredEvent::get();
    assertCount(1, $otherStoredEvents);

    $storedEvent = $otherStoredEvents->first();
    assertEquals($this->aggregateUuid, $storedEvent->aggregate_uuid);

    $event = $storedEvent->event;
    assertInstanceOf(MoneyAdded::class, $event);
    assertEquals(100, $event->amount);
});

test('when an the config specifies a stored event model persisting will persist all events it recorded via stored event', function () {
    config()->set('event-sourcing.stored_event_model', OtherEloquentStoredEvent::class);

    AccountAggregateRoot::retrieve($this->aggregateUuid)
        ->addMoney(100)
        ->persist();

    $storedEvents = EloquentStoredEvent::get();
    assertCount(0, $storedEvents);

    $otherStoredEvents = OtherEloquentStoredEvent::get();
    assertCount(1, $otherStoredEvents);

    $storedEvent = $otherStoredEvents->first();
    assertEquals($this->aggregateUuid, $storedEvent->aggregate_uuid);

    $event = $storedEvent->event;
    assertInstanceOf(MoneyAdded::class, $event);
    assertEquals(100, $event->amount);
});

test('it throws an error when defining a class that doesnt extend eloquent stored event', function () {
    config()->set('event-sourcing.stored_event_model', InvalidEloquentStoredEvent::class);

    AccountAggregateRoot::retrieve($this->aggregateUuid)
        ->addMoney(100)
        ->persist();
})->throws(InvalidEloquentStoredEventModel::class);

test('when retrieving an aggregate root all events will be replayed to it', function () {
    /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    $aggregateRoot
        ->addMoney(100)
        ->addMoney(200)
        ->addMoney(300);

    $aggregateRoot->persist();

    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    assertEquals(600, $aggregateRoot->balance);
});

test('when applying events it increases the version number', function () {
    /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    $aggregateRoot
        ->addMoney(100)
        ->addMoney(100)
        ->addMoney(100);

    assertEquals(3, $aggregateRoot->aggregateVersion);
});

test('snapshotting stores public properties and version number', function () {
    /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    $aggregateRoot
        ->addMoney(100)
        ->addMoney(100)
        ->addMoney(100);

    assertEquals(0, EloquentSnapshot::count());

    $aggregateRoot->snapshot();

    assertEquals(1, EloquentSnapshot::count());
    tap(EloquentSnapshot::first(), function (EloquentSnapshot $snapshot) {
        assertEquals(300, $snapshot->state['balance']);
        assertEquals(3, $snapshot->aggregate_version);
    });
});

test('restoring an aggregate root with a snapshot restores public properties', function () {
    /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    $aggregateRoot
        ->addMoney(100)
        ->addMoney(100)
        ->addMoney(100);

    $aggregateRoot->snapshot();

    $aggregateRootRetrieved = AccountAggregateRoot::retrieve($this->aggregateUuid);

    assertEquals(3, $aggregateRootRetrieved->aggregateVersion);
    assertEquals(300, $aggregateRootRetrieved->balance);
});

test('events saved after the snapshot are reconstituted', function () {
    /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    $aggregateRoot
        ->addMoney(100)
        ->addMoney(100)
        ->addMoney(100)
        ->persist();

    $aggregateRoot->snapshot();
    $aggregateRoot->addMoney(100)->persist();

    $aggregateRootRetrieved = AccountAggregateRoot::retrieve($this->aggregateUuid);

    assertEquals(4, $aggregateRootRetrieved->aggregateVersion);
    assertEquals(400, $aggregateRootRetrieved->balance);
});

test('when retrieving an aggregate root all events will be replayed to it in the correct order', function () {
    /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    $aggregateRoot
        ->multiplyMoney(5)
        ->addMoney(100);

    $aggregateRoot->persist();

    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    assertEquals(100, $aggregateRoot->balance);
});

test('when retrieving an aggregate root all events will be replayed to it with the stored event repository specified', function () {
    /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRootWithStoredEventRepositorySpecified $aggregateRoot */
    $aggregateRoot = AccountAggregateRootWithStoredEventRepositorySpecified::retrieve($this->aggregateUuid);

    $aggregateRoot
        ->addMoney(100)
        ->addMoney(100)
        ->addMoney(100);

    $aggregateRoot->persist();

    assertEquals(0, EloquentStoredEvent::count());
    assertEquals(3, OtherEloquentStoredEvent::count());

    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);
    assertEquals(0, $aggregateRoot->balance);

    $aggregateRoot = AccountAggregateRootWithStoredEventRepositorySpecified::retrieve($this->aggregateUuid);
    assertEquals(300, $aggregateRoot->balance);
});

test('a recorded event immediately gets applied', function () {
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);
    $aggregateRoot->addMoney(123);

    assertEquals(123, $aggregateRoot->balance);
});

test('it can persist aggregate roots in a transaction', function () {
    Mail::fake();

    Projectionist::addProjector(AccountProjector::class);
    Projectionist::addReactor(SendMailReactor::class);

    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid)->addMoney(123);
    AggregateRoot::persistInTransaction($aggregateRoot);

    assertCount(1, EloquentStoredEvent::get());
    assertCount(1, Account::get());
    Mail::assertSent(MoneyAddedMailable::class);
});

test('it will not call any event handlers when persisting fails', function () {
    Mail::fake();

    Projectionist::addProjector(AccountProjector::class);
    Projectionist::addReactor(SendMailReactor::class);

    $aggregateRoot = AccountAggregateRootWithFailingPersist::retrieve($this->aggregateUuid)->addMoney(123);

    expect(fn () => AggregateRoot::persistInTransaction($aggregateRoot))->toThrow(Exception::class);

    assertCount(0, EloquentStoredEvent::get());
    assertCount(0, Account::get());
    Mail::assertNothingSent();
});

test('reactors will get called when an aggregate root is persisted', function () {
    Projectionist::addReactor(SendMailReactor::class);

    Mail::fake();

    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    $aggregateRoot->addMoney(123);

    Mail::assertNothingSent();

    $aggregateRoot->persist();

    Mail::assertSent(MoneyAddedMailable::class, function (MoneyAddedMailable $mailable) {
        assertEquals($this->aggregateUuid, $mailable->aggregateUuid);
        assertEquals(123, $mailable->amount);

        return true;
    });
});

test('it will throw an exception if the latest stored version id is not what we expect', function () {
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);
    $aggregateRoot->addMoney(100);

    $aggregateRootInAnotherRequest = AccountAggregateRoot::retrieve($this->aggregateUuid);
    $aggregateRootInAnotherRequest->addMoney(100);
    $aggregateRootInAnotherRequest->persist();

    $aggregateRoot->persist();
})->throws(CouldNotPersistAggregate::class);

test('it fires the triggered events on the event bus when configured', function () {
    config()->set('event-sourcing.dispatch_events_from_aggregate_roots', true);

    Event::fake([
        MoneyAdded::class,
    ]);

    AccountAggregateRoot::retrieve($this->aggregateUuid)
        ->addMoney(100)
        ->persist();

    Event::assertDispatched(MoneyAdded::class, function (MoneyAdded $event) {
        assertEquals(100, $event->amount);
        assertTrue($event->firedFromAggregateRoot);

        return true;
    });
});

test('when an apply method is public it can have additional dependencies', function () {
    config()->set('event-sourcing.dispatch_events_from_aggregate_roots', true);

    Event::fake([
        MoneyMultiplied::class,
    ]);

    AccountAggregateRoot::retrieve($this->aggregateUuid)
        ->multiplyMoney(100)
        ->persist();

    Event::assertDispatched(MoneyMultiplied::class);
});

test('it can load the uuid', function () {
    $aggregateRoot = (new AccountAggregateRoot())->loadUuid($this->aggregateUuid);

    assertEquals($this->aggregateUuid, $aggregateRoot->uuid());
})->skip();

test('it persists when uuid is loaded', function () {
    app(AccountAggregateRoot::class)
        ->loadUuid($this->aggregateUuid)
        ->addMoney(100)
        ->persist();

    $storedEvents = EloquentStoredEvent::get();
    assertCount(1, $storedEvents);

    $storedEvent = $storedEvents->first();
    assertEquals($this->aggregateUuid, $storedEvent->aggregate_uuid);

    $event = $storedEvent->event;
    assertInstanceOf(MoneyAdded::class, $event);
    assertEquals(100, $event->amount);
});

test('created at is set from within the aggregate root', function () {
    $now = CarbonImmutable::make('2021-02-01 00:00:00');

    CarbonImmutable::setTestNow($now);

    app(AccountAggregateRoot::class)
        ->loadUuid($this->aggregateUuid)
        ->addMoney(100)
        ->persist();

    /** @var \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent $eloquentEvent */
    $eloquentEvent = EloquentStoredEvent::first();

    $event = $eloquentEvent->toStoredEvent()->event;

    assertTrue($now->eq($event->createdAt()));
});
