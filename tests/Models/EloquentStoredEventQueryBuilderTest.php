<?php

namespace Spatie\EventSourcing\Tests\Models;

use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestCase;

class EloquentStoredEventQueryBuilderTest extends TestCase
{
    /** @test */
    public function it_constrains_to_property_value()
    {
        $expected = EloquentStoredEvent::query()->whereJsonContains(
            column: 'event_properties->otherEntityId', value: 10
        );

        $actual = EloquentStoredEvent::query()->wherePropertyIs(
            property: 'otherEntityId', value: 10
        );

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function it_constrains_to_property_value_difference()
    {
        $expected = EloquentStoredEvent::query()->whereJsonDoesntContain(
            column: 'event_properties->name', value: 'Johnson'
        );

        $actual = EloquentStoredEvent::query()->wherePropertyIsNot(
            property: 'name', value: 'Johnson'
        );

        $this->assertEquals($expected, $actual);
    }
}
