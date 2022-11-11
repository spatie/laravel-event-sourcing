<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\DummyAggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\DummyEvent;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;

it('can determine that no events were recorded', function () {
    DummyAggregateRoot::fake()->assertNothingRecorded();
});

it('ignores given events in assert nothing recorded', function () {
    DummyAggregateRoot::fake()
        ->given([new DummyEvent(123)])
        ->assertNothingRecorded();
});

it('ignores given events in assert recorded', function () {
    DummyAggregateRoot::fake()
        ->given([new DummyEvent(1)])
        ->when(function (DummyAggregateRoot $dummyAggregateRoot) {
            $dummyAggregateRoot->dummy();
        })
        ->assertRecorded([new DummyEvent(2)]);
});

it('ignores given events in assert not recorded', function () {
    DummyAggregateRoot::fake()
        ->given([new DummyEvent(123)])
        ->assertNotRecorded(DummyEvent::class);
});

it('can retrieve an aggregate for a given uuid', function () {
    $fakeUuid = 'fake-uuid';

    $realAggregateRoot = DummyAggregateRoot::fake($fakeUuid)->aggregateRoot();

    assertEquals($fakeUuid, $realAggregateRoot->uuid());
});

it('will apply the given events', function () {
    DummyAggregateRoot::fake()
        ->given([
            new DummyEvent(123),
        ])
        ->when(function (DummyAggregateRoot $dummyAggregateRoot) {
            $dummyAggregateRoot->dummy();

            assertEquals(123 + 1, $dummyAggregateRoot->getLatestInteger());
        });
});

it('can assert the recorded events', function () {
    DummyAggregateRoot::fake()
        ->given([
            new DummyEvent(1),
            new DummyEvent(2),
        ])
        ->when(function (DummyAggregateRoot $dummyAggregateRoot) {
            $dummyAggregateRoot->dummy();
        })
        ->assertRecorded([
            new DummyEvent(3),
        ]);
});

it('can assert the recorded events with a closure', function () {
    DummyAggregateRoot::fake()
        ->given([
            new DummyEvent(1),
            new DummyEvent(2),
        ])
        ->when(function (DummyAggregateRoot $dummyAggregateRoot) {
            $dummyAggregateRoot->dummy();
        })
        ->assertRecorded(function (DummyEvent $event) {
            assertEquals(3, $event->integer);
        });
});

it('should return values which are captured and passed to assert that', function () {
    DummyAggregateRoot::fake()
        ->when(function (DummyAggregateRoot $dummyAggregateRoot) {
            return $dummyAggregateRoot->uuid();
        })
        ->then(function ($uuid) {
            assertNotNull($uuid);
        });
});

it('should return a boolean', function () {
    DummyAggregateRoot::fake()
        ->when(function (DummyAggregateRoot $dummyAggregateRoot) {
            return $dummyAggregateRoot->uuid();
        })
        ->then(function ($uuid) {
            return true;
        });
});

it('can assert the applied events', function () {
    DummyAggregateRoot::fake()
        ->given([
            new DummyEvent(1),
            new DummyEvent(2),
        ])
        ->when(function (DummyAggregateRoot $dummyAggregateRoot) {
            $dummyAggregateRoot->dummy();

            $dummyAggregateRoot->persist();
        })
        ->assertApplied([
            new DummyEvent(1),
            new DummyEvent(2),
            new DummyEvent(3),
        ]);
});

it('can assert recorded events without using when', function () {
    /** @var \Spatie\EventSourcing\Tests\TestClasses\DummyAggregateRoot|\Spatie\EventSourcing\AggregateRoots\FakeAggregateRoot $fakeAggregateRoot */
    $fakeAggregateRoot = DummyAggregateRoot::fake();

    $fakeAggregateRoot->given([
        new DummyEvent(1),
        new DummyEvent(2),
    ]);

    $fakeAggregateRoot->dummy();

    $fakeAggregateRoot->assertRecorded([
        new DummyEvent(3),
    ]);
});

it('can assert an specific event is recorded when multiple events are fired', function () {
    DummyAggregateRoot::fake()
        ->given([
            new DummyEvent(1),
            new DummyEvent(2),
        ])
        ->when(function (DummyAggregateRoot $dummyAggregateRoot) {
            $dummyAggregateRoot->dummy();
            $dummyAggregateRoot->dummy();
        })
        ->assertEventRecorded(new DummyEvent(3));
});

it('can assert that an event is not recorded', function () {
    DummyAggregateRoot::fake()->assertNotRecorded(DummyEvent::class);

    DummyAggregateRoot::fake()->assertNotRecorded([DummyEvent::class]);
});

it('can assert that an event is not applied', function () {
    DummyAggregateRoot::fake()->assertNotApplied(DummyEvent::class);

    DummyAggregateRoot::fake()->assertNotApplied([DummyEvent::class]);
});

it('can assert that noting is applied', function () {
    DummyAggregateRoot::fake()->assertNothingApplied();
});
