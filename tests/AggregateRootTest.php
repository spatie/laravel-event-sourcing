<?php

namespace Spatie\EventSourcing\Tests;

use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

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

beforeEach(function () {
    $this->aggregateUuid = FakeUuid::generate();
});

it('can resolve the aggregate root dependencies from the container', function () {
    $this->app->bind(AccountAggregateRoot::class, function () {
        return new AccountAggregateRoot(app(Math::class), 42);
    });

    $root = AccountAggregateRoot::retrieve($this->aggregateUuid);

    assertEquals(42, $root->dependency);
});

it('can get the uuid', function () {
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    assertEquals($this->aggregateUuid, $aggregateRoot->uuid());
});

it('should persist all events it recorded when persisting an aggregate root will', function () {
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

it('should persist all events it recorded via repository when an aggregate root specifies a stored event repository', function () {
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

it('should persist all events it recorded via stored event when the config specifies a stored event model', function () {
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

it('throws an error when defining a class that doesnt extend eloquent stored event', function () {
    config()->set('event-sourcing.stored_event_model', InvalidEloquentStoredEvent::class);

    AccountAggregateRoot::retrieve($this->aggregateUuid)
        ->addMoney(100)
        ->persist();
})->throws(InvalidEloquentStoredEventModel::class);

it('should replay all events when retrieving an aggregate root', function () {
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

it('should increase the version number when applying events', function () {
    /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    $aggregateRoot
        ->addMoney(100)
        ->addMoney(100)
        ->addMoney(100);

    assertEquals(3, $aggregateRoot->aggregateVersion);
});

it('should store public properties and version number when snapshotting', function () {
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

it('should restore public properties when restoring an aggregate root with a snapshot', function () {
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

it('should save events after the snapshot are reconstituted', function () {
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

it('should replay all events in the correct order when retrieving an aggregate root all', function () {
    /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    $aggregateRoot
        ->multiplyMoney(5)
        ->addMoney(100);

    $aggregateRoot->persist();

    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

    assertEquals(100, $aggregateRoot->balance);
});

it('should replay all events with the stored event repository specified when retrieving an aggregate root', function () {
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

it('should apply a recorded event immediately', function () {
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);
    $aggregateRoot->addMoney(123);

    assertEquals(123, $aggregateRoot->balance);
});

it('can persist aggregate roots in a transaction', function () {
    Mail::fake();

    Projectionist::addProjector(AccountProjector::class);
    Projectionist::addReactor(SendMailReactor::class);

    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid)->addMoney(123);
    AggregateRoot::persistInTransaction($aggregateRoot);

    assertCount(1, EloquentStoredEvent::get());
    assertCount(1, Account::get());
    Mail::assertSent(MoneyAddedMailable::class);
});

it('will not call any event handlers when persisting fails', function () {
    Mail::fake();

    Projectionist::addProjector(AccountProjector::class);
    Projectionist::addReactor(SendMailReactor::class);

    $aggregateRoot = AccountAggregateRootWithFailingPersist::retrieve($this->aggregateUuid)->addMoney(123);

    expect(fn () => AggregateRoot::persistInTransaction($aggregateRoot))->toThrow(Exception::class);

    assertCount(0, EloquentStoredEvent::get());
    assertCount(0, Account::get());
    Mail::assertNothingSent();
});

it('should call reactors when an aggregate root is persisted', function () {
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

it('will throw an exception if the latest stored version id is not what we expect', function () {
    $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);
    $aggregateRoot->addMoney(100);

    $aggregateRootInAnotherRequest = AccountAggregateRoot::retrieve($this->aggregateUuid);
    $aggregateRootInAnotherRequest->addMoney(100);
    $aggregateRootInAnotherRequest->persist();

    $aggregateRoot->persist();
})->throws(CouldNotPersistAggregate::class);

it('fires the triggered events on the event bus when configured', function () {
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

it('can have additional dependencies when an apply method is public', function () {
    config()->set('event-sourcing.dispatch_events_from_aggregate_roots', true);

    Event::fake([
        MoneyMultiplied::class,
    ]);

    AccountAggregateRoot::retrieve($this->aggregateUuid)
        ->multiplyMoney(100)
        ->persist();

    Event::assertDispatched(MoneyMultiplied::class);
});

it('can load the uuid', function () {
    $aggregateRoot = (new AccountAggregateRoot())->loadUuid($this->aggregateUuid);

    assertEquals($this->aggregateUuid, $aggregateRoot->uuid());
})->skip();

it('persists when uuid is loaded', function () {
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

it('should set created at from within the aggregate root', function () {
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
