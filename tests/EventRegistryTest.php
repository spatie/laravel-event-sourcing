<?php

namespace Spatie\EventSourcing\Tests;

use function PHPUnit\Framework\assertEquals;
use function Spatie\Snapshots\assertMatchesSnapshot;

use Spatie\EventSourcing\Facades\EventRegistry;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithAlias;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithCustomAlias;
use Spatie\EventSourcing\Tests\TestClasses\Events\TestEvent;

it('sets event class map as is', function () {
    EventRegistry::setClassMap($classMap = [
        'entry-1' => TestEvent::class,
        'entry-2' => EventWithAlias::class,
        'entry-3' => EventWithCustomAlias::class,
    ]);

    assertEquals($classMap, EventRegistry::getClassMap()->all());
});

it('passes through alias if corresponding event class has not been found in registry', function () {
    $eventClass = EventRegistry::getEventClass('event-alias');
    assertEquals('event-alias', $eventClass);
});

it('passes through event class if corresponding alias has not been found in registry', function () {
    $alias = EventRegistry::getAlias(TestEvent::class);
    assertEquals(TestEvent::class, $alias);

    $alias = EventRegistry::getAlias(EventWithAlias::class);
    assertEquals(EventWithAlias::class, $alias);
});

it('will set the default alias based on class name while adding event to the registry', function () {
    EventRegistry::addEventClass(TestEvent::class);

    $eventClass = EventRegistry::getEventClass('test-event');
    assertEquals(TestEvent::class, $eventClass);

    $alias = EventRegistry::getAlias(TestEvent::class);
    assertEquals('test-event', $alias);
});

it('will set the custom alias while adding event to the registry', function () {
    EventRegistry::addEventClass(TestEvent::class, 'event-with-custom-alias');

    $eventClass = EventRegistry::getEventClass('event-with-custom-alias');
    assertEquals(TestEvent::class, $eventClass);

    $alias = EventRegistry::getAlias(TestEvent::class);
    assertEquals('event-with-custom-alias', $alias);
});

it('will set the alias from attribute while adding event to the registry', function () {
    EventRegistry::addEventClass(EventWithAlias::class);

    $eventClass = EventRegistry::getEventClass('event-with-alias-from-attribute');
    assertEquals(EventWithAlias::class, $eventClass);

    $alias = EventRegistry::getAlias(EventWithAlias::class);
    assertEquals('event-with-alias-from-attribute', $alias);
});

it('will prioritize custom alias over alias defined in attribute', function () {
    EventRegistry::addEventClass(EventWithAlias::class, 'event-with-custom-alias');

    $eventClass = EventRegistry::getEventClass('event-with-custom-alias');
    assertEquals(EventWithAlias::class, $eventClass);

    $alias = EventRegistry::getAlias(EventWithAlias::class);
    assertEquals('event-with-custom-alias', $alias);
});

it('will set the alias from eventName method while adding event to the registry', function () {
    EventRegistry::addEventClass(EventWithCustomAlias::class);

    $eventClass = EventRegistry::getEventClass('event-with-alias-from-method');
    assertEquals(EventWithCustomAlias::class, $eventClass);

    $alias = EventRegistry::getAlias(EventWithCustomAlias::class);
    assertEquals('event-with-alias-from-method', $alias);
});

it('wont fall back to default name resolution logic unless instructed explicitly', function () {
    EventRegistry::addEventClass(EventWithCustomAlias::class, 'event-with-custom-alias');

    $eventClass = EventRegistry::getEventClass('event-with-alias-from-method');
    assertEquals(EventWithCustomAlias::class, $eventClass);

    $alias = EventRegistry::getAlias(EventWithCustomAlias::class);
    assertEquals('event-with-alias-from-method', $alias);
});

it('wont override already existing entry', function () {
    EventRegistry::addEventClass(TestEvent::class, 'alias-1');
    EventRegistry::addEventClass(TestEvent::class, 'alias-2');

    $eventClass = EventRegistry::getEventClass('alias-1');
    assertEquals(TestEvent::class, $eventClass);

    $eventClass = EventRegistry::getEventClass('alias-2');
    assertEquals('alias-2', $eventClass);

    $alias = EventRegistry::getAlias(TestEvent::class);
    assertEquals('alias-1', $alias);
});

it('can register multiple event classes at once', function () {
    EventRegistry::addEventClasses([
        'custom-alias' => TestEvent::class,
        EventWithAlias::class,
        'this-record-wont-add' => EventWithAlias::class,
        'this-alias-wont-be-used' => EventWithCustomAlias::class,
    ]);

    $classMap = EventRegistry::getClassMap()->all();
    assertMatchesSnapshot($classMap);
});
