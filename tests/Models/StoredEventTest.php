<?php

namespace Spatie\EventSourcing\Tests\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Spatie\EventSourcing\Enums\MetaData;
use Spatie\EventSourcing\EventSerializers\EventSerializer;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\Exceptions\InvalidStoredEvent;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertEqualsCanonicalizing;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

function fireEvents(int $number = 1, string $className = MoneyAddedEvent::class)
{
    foreach (range(1, $number) as $i) {
        event(new $className(test()->account, 1234));
    }
}

beforeEach(function () {
    Projectionist::addProjector(new BalanceProjector());

    $this->account = Account::create();
});

it('has a scope to get all events starting from given id', function () {
    fireEvents(4);

    assertEquals([3, 4], EloquentStoredEvent::startingFrom(3)->pluck('id')->toArray());
});

it('will throw a human readable exception when the event couldnt be deserialized', function () {
    fireEvents();

    // sneakily change the stored event class
    EloquentStoredEvent::first()->update(['event_class' => 'NonExistingClass']);

    EloquentStoredEvent::first()->toStoredEvent();
})->throws(InvalidStoredEvent::class);

it('will store the alias when a classname is found in the event class map', function () {
    $this->setConfig('event-sourcing.event_class_map', [
        'money_added' => MoneyAddedEvent::class,
    ]);

    fireEvents();

    assertEquals(MoneyAddedEvent::class, EloquentStoredEvent::first()->toStoredEvent()->event_class);
    $this->assertDatabaseHas('stored_events', ['event_class' => 'money_added']);
});

it('allows to modify metadata with offset set in eloquent model', function () {
    EloquentStoredEvent::creating(function (EloquentStoredEvent $event) {
        $event->meta_data->set('ip', '127.0.0.1');
    });

    $this->setConfig('event-sourcing.event_class_map', [
        'money_added' => MoneyAddedEvent::class,
    ]);

    fireEvents();

    $instance = EloquentStoredEvent::withMetaDataAttributes('ip', '127.0.0.1')->first();

    assertInstanceOf(EloquentStoredEvent::class, $instance);
    assertArrayHasKey('ip', $instance->meta_data->toArray());
    assertSame('127.0.0.1', $instance->meta_data['ip']);

    EloquentStoredEvent::flushEventListeners();
});

it('can handle an encoded string as event properties', function () {
    fireEvents();

    $eloquentEvent = EloquentStoredEvent::first();

    $storedEvent = new StoredEvent([
        'id' => $eloquentEvent->id,
        'event_properties' => json_encode($eloquentEvent->event_properties),
        'aggregate_uuid' => $eloquentEvent->aggregate_uuid ?? '',
        'aggregate_version' => $eloquentEvent->aggregate_version ?? 0,
        'event_class' => $eloquentEvent->event_class,
        'meta_data' => $eloquentEvent->meta_data,
        'created_at' => $eloquentEvent->created_at,
    ]);

    assertInstanceOf(MoneyAddedEvent::class, $storedEvent->event);
});

it('encodes the event properties itself when its an array', function () {
    fireEvents();

    $eloquentEvent = EloquentStoredEvent::first();

    assertIsArray($eloquentEvent->event_properties);

    $storedEvent = new StoredEvent([
        'id' => $eloquentEvent->id,
        'event_properties' => $eloquentEvent->event_properties,
        'aggregate_uuid' => $eloquentEvent->aggregate_uuid ?? '',
        'aggregate_version' => $eloquentEvent->aggregate_version ?? 0,
        'event_class' => $eloquentEvent->event_class,
        'meta_data' => $eloquentEvent->meta_data,
        'created_at' => $eloquentEvent->created_at,
    ]);

    assertInstanceOf(MoneyAddedEvent::class, $storedEvent->event);
});

it('exposes the aggregate version', function () {
    fireEvents();

    $eloquentEvent = EloquentStoredEvent::first();

    $storedEvent = $eloquentEvent->toStoredEvent();

    assertSame('0', $storedEvent->aggregate_version);
});

it('uses the original event if set', function () {
    $originalEvent = new MoneyAdded(100);

    $eloquentStoredEvent = new EloquentStoredEvent();

    $eloquentStoredEvent->setOriginalEvent($originalEvent);

    assertSame($originalEvent, $eloquentStoredEvent->event);
});

it('updates the original if meta data is changed', function () {
    $originalEvent = new MoneyAdded(100);

    $eloquentStoredEvent = new EloquentStoredEvent();

    $eloquentStoredEvent->setOriginalEvent($originalEvent);

    $eloquentStoredEvent->meta_data->set('user.id', 1);

    assertEqualsCanonicalizing(
        $eloquentStoredEvent->meta_data->toArray(),
        $eloquentStoredEvent->event->metaData()
    );
});

it('updates the original if meta data is changed and saved', function () {
    $originalEvent = new MoneyAdded(100);

    $eloquentStoredEvent = new EloquentStoredEvent();

    $eloquentStoredEvent->setOriginalEvent($originalEvent);

    $createdAt = Carbon::now();

    $eloquentStoredEvent->setRawAttributes([
        'event_properties' => app(EventSerializer::class)->serialize(clone $originalEvent),
        'aggregate_uuid' => Str::uuid(),
        'aggregate_version' => 1,
        'event_version' => 1,
        'event_class' => MoneyAdded::class,
        'meta_data' => json_encode($originalEvent->metaData() + [
                MetaData::CREATED_AT => $createdAt->toDateTimeString(),
            ]),
        'created_at' => $createdAt,
    ]);

    $eloquentStoredEvent->save();

    $eloquentStoredEvent->meta_data->set('user.id', 1);

    $eloquentStoredEvent->save();

    assertEqualsCanonicalizing(
        $eloquentStoredEvent->meta_data->toArray(),
        $eloquentStoredEvent->event->metaData()
    );
});

it('should set created at on event', function () {
    $now = Carbon::make('2021-01-01 10:00:00');

    Carbon::setTestNow($now);

    fireEvents();

    /** @var \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent $eloquentEvent */
    $eloquentEvent = EloquentStoredEvent::first();

    $event = $eloquentEvent->toStoredEvent()->event;

    assertTrue($event->createdAt()->eq($now));
});

it('should set the stored event id', function () {
    fireEvents();

    /** @var \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent $eloquentEvent */
    $eloquentEvent = EloquentStoredEvent::first();

    $event = $eloquentEvent->toStoredEvent()->event;

    assertEquals($eloquentEvent->id, $event->storedEventId());
});
