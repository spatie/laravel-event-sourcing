<?php

namespace Spatie\EventSourcing\Tests\Models;

use Carbon\Carbon;
use Illuminate\Support\InteractsWithTime;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertSame;

use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithCarbon;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithDatetime;

uses(InteractsWithTime::class);

it('constrains to property value', function () {
    $expected = EloquentStoredEvent::query()->whereJsonContains(
        column: 'event_properties->otherEntityId',
        value: 10
    );

    $actual = EloquentStoredEvent::query()->wherePropertyIs(
        property: 'otherEntityId',
        value: 10
    );

    assertEquals($expected, $actual);
});

it('constrains to property value difference', function () {
    $expected = EloquentStoredEvent::query()->whereJsonDoesntContain(
        column: 'event_properties->name',
        value: 'Johnson'
    );

    $actual = EloquentStoredEvent::query()->wherePropertyIsNot(
        property: 'name',
        value: 'Johnson'
    );

    assertEquals($expected, $actual);
});

it('retrieves last event', function () {
    $this->travelTo(now()->subMinutes(10), fn () => event(new MoneyAdded(10)));
    $this->travelTo(now()->subMinutes(5), fn () => event(new MoneyAdded(100)));

    event(new MoneyAdded(1000));

    $lastEvent = EloquentStoredEvent::query()->lastEvent();
    /** @var MoneyAdded $storedEvent */
    $storedEvent = $lastEvent->toStoredEvent()->event;

    assertInstanceOf(EloquentStoredEvent::class, $lastEvent);
    assertSame(1000, $storedEvent->amount);
});

it('retrieve last event of type', function () {
    $date = now()
        ->subDays(3)
        ->setTime(0, 0, 0)
        ->toDateTimeImmutable();

    $this->travelTo(
        now()->subMinutes(10),
        fn () => event(new MoneyAdded(10))
    );
    $this->travelTo(
        now()->subMinutes(10),
        fn () => event(new EventWithDatetime($date))
    );

    event(new MoneyAdded(10));

    $event = EloquentStoredEvent::query()->lastEvent(EventWithDatetime::class);
    $storedEvent = $event->toStoredEvent()->event;

    assertInstanceOf(EventWithDatetime::class, $storedEvent);
    assertEquals($date, $storedEvent->value);
});

it('retrieves last event of multiple types', function () {
    $date = now()->subDays(3)->setTime(0, 0, 0);

    $this->travelTo(
        now()->subMinutes(10),
        fn () => event(new MoneyAdded(10))
    );
    $this->travelTo(
        now()->subMinutes(2),
        fn () => event(new EventWithDatetime(now()->toDateTimeImmutable()))
    );

    event(new EventWithCarbon($date));

    $event = EloquentStoredEvent::query()->lastEvent(
        EventWithDatetime::class,
        EventWithCarbon::class
    );
    $storedEvent = $event->toStoredEvent()->event;

    assertInstanceOf(EventWithCarbon::class, $storedEvent);
    assertEquals($date, $storedEvent->value);
});

it('retrieves last event of type when two were created at the same time', function () {
    Carbon::setTestNow();

    event(new MoneyAdded(50));
    event(new MoneyAdded(10));

    $event = EloquentStoredEvent::query()->lastEvent(MoneyAdded::class);
    /** @var MoneyAdded $storedEvent */
    $storedEvent = $event->toStoredEvent()->event;

    assertInstanceOf(MoneyAdded::class, $storedEvent);
    assertEquals(10, $storedEvent->amount);
});
