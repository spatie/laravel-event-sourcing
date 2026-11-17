<?php

namespace Spatie\EventSourcing\Tests;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\EventWithArray;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\SpecialMoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\TestEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ChildEventHandlingMethodsProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\EventHandlingMethodsProjector;

function storedEventFor(ShouldBeStored $event): StoredEvent
{
    return new StoredEvent([
        'event_properties' => '{}',
        'aggregate_uuid' => '',
        'aggregate_version' => 0,
        'event_class' => $event::class,
        'meta_data' => [],
        'created_at' => now(),
    ], $event);
}

beforeEach(function () {
    EventHandlingMethodsProjector::$log = [];
});

it('handles an event only through public and protected methods that do not accept null', function (ShouldBeStored $event, bool $handled, array $calledMethods) {
    $projector = new EventHandlingMethodsProjector();

    assertSame($handled, $projector->handles(storedEventFor($event)));

    $projector->handle(storedEventFor($event));

    assertEquals($calledMethods, EventHandlingMethodsProjector::$log);
})->with([
    'the class a method takes' => [fn () => new MoneyAddedEvent(new Account(), 10), true, ['onMoneyAdded', 'onMoneyChanged']],
    'a subclass of the class a method takes' => [fn () => new SpecialMoneyAddedEvent(new Account(), 10), true, ['onMoneyAdded', 'onMoneyChanged']],
    'a class in a union a method takes' => [fn () => new MoneySubtractedEvent(new Account(), 10), true, ['onMoneyChanged']],
    'a class only a nullable method takes' => [fn () => new TestEvent(), false, []],
    'a class only a private method takes' => [fn () => new EventWithArray([]), false, []],
]);

it('gives the same answer every time it is asked', function () {
    $projector = new EventHandlingMethodsProjector();
    $event = new MoneySubtractedEvent(new Account(), 10);

    assertTrue($projector->handles(storedEventFor($event)));
    assertTrue((new EventHandlingMethodsProjector())->handles(storedEventFor($event)));

    $projector->handle(storedEventFor($event));
    $projector->handle(storedEventFor($event));

    assertEquals(['onMoneyChanged', 'onMoneyChanged'], EventHandlingMethodsProjector::$log);
});

it('looks up the methods for each handler class separately', function () {
    $event = new TestEvent();

    assertFalse((new EventHandlingMethodsProjector())->handles(storedEventFor($event)));
    assertTrue((new ChildEventHandlingMethodsProjector())->handles(storedEventFor($event)));

    (new EventHandlingMethodsProjector())->handle(storedEventFor($event));
    (new ChildEventHandlingMethodsProjector())->handle(storedEventFor($event));

    assertEquals(['onTestEvent'], EventHandlingMethodsProjector::$log);
});
