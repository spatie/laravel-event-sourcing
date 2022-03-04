<?php

namespace Spatie\EventSourcing\Tests\Models;

use Illuminate\Support\InteractsWithTime;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestCase;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithCarbon;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithDatetime;

class EloquentStoredEventQueryBuilderTest extends TestCase
{
    use InteractsWithTime;

    /** @test */
    public function it_constrains_to_property_value()
    {
        $expected = EloquentStoredEvent::query()->whereJsonContains(
            column: 'event_properties->otherEntityId',
            value: 10
        );

        $actual = EloquentStoredEvent::query()->wherePropertyIs(
            property: 'otherEntityId',
            value: 10
        );

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function it_constrains_to_property_value_difference()
    {
        $expected = EloquentStoredEvent::query()->whereJsonDoesntContain(
            column: 'event_properties->name',
            value: 'Johnson'
        );

        $actual = EloquentStoredEvent::query()->wherePropertyIsNot(
            property: 'name',
            value: 'Johnson'
        );

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function it_retrieves_last_event()
    {
        $this->travelTo(now()->subMinutes(10), fn() => event(new MoneyAdded(10)));
        $this->travelTo(now()->subMinutes(5), fn() => event(new MoneyAdded(100)));

        event(new MoneyAdded(1000));

        $lastEvent = EloquentStoredEvent::query()->lastEvent();
        /** @var MoneyAdded $storedEvent */
        $storedEvent = $lastEvent->toStoredEvent()->event;

        $this->assertInstanceOf(EloquentStoredEvent::class, $lastEvent);
        $this->assertSame(1000, $storedEvent->amount);
    }

    /** @test */
    public function it_retrieve_last_event_of_type()
    {
        $date = now()
            ->subDays(3)
            ->setTime(0, 0, 0)
            ->toDateTimeImmutable();

        $this->travelTo(
            now()->subMinutes(10), fn() => event(new MoneyAdded(10))
        );
        $this->travelTo(
            now()->subMinutes(10), fn() => event(new EventWithDatetime($date))
        );

        event(new MoneyAdded(10));

        $event = EloquentStoredEvent::query()->lastEvent(EventWithDatetime::class);
        $storedEvent = $event->toStoredEvent()->event;

        $this->assertInstanceOf(EventWithDatetime::class, $storedEvent);
        $this->assertEquals($date, $storedEvent->value);
    }

    /** @test */
    public function it_retrieves_last_event_of_multiple_types()
    {
        $date = now()->subDays(3)->setTime(0, 0, 0);

        $this->travelTo(
            now()->subMinutes(10), fn() => event(new MoneyAdded(10))
        );
        $this->travelTo(
            now()->subMinutes(2), fn() => event(new EventWithDatetime(now()->toDateTimeImmutable()))
        );

        event(new EventWithCarbon($date));

        $event = EloquentStoredEvent::query()->lastEvent(
            EventWithDatetime::class,
            EventWithCarbon::class
        );
        $storedEvent = $event->toStoredEvent()->event;

        $this->assertInstanceOf(EventWithCarbon::class, $storedEvent);
        $this->assertEquals($date, $storedEvent->value);
    }
}
