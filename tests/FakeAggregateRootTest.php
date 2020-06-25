<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\DummyAggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\DummyEvent;

class FakeAggregateRootTest extends TestCase
{
    /** @test */
    public function it_can_determine_that_no_events_were_recorded()
    {
        DummyAggregateRoot::fake()->assertNothingRecorded();
    }

    /** @test */
    public function it_can_retrieve_an_aggregate_for_a_given_uuid()
    {
        $fakeUuid = 'fake-uuid';

        $realAggregateRoot = DummyAggregateRoot::fake($fakeUuid)->aggregateRoot();

        $this->assertEquals($fakeUuid, $realAggregateRoot->uuid());
    }

    /** @test */
    public function it_will_apply_the_given_events()
    {
        DummyAggregateRoot::fake()
            ->given([
                new DummyEvent(123),
            ])
            ->when(function (DummyAggregateRoot $dummyAggregateRoot) {
                $dummyAggregateRoot->dummy();

                $this->assertEquals(123 + 1, $dummyAggregateRoot->getLatestInteger());
            });
    }

    /** @test */
    public function it_can_assert_the_recorded_events()
    {
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
    }

    /** @test */
    public function it_can_assert_the_applied_events()
    {
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
    }

    /** @test */
    public function it_can_assert_recorded_events_without_using_when()
    {
        /** @var \Spatie\EventSourcing\Tests\TestClasses\DummyAggregateRoot|\Spatie\EventSourcing\FakeAggregateRoot $fakeAggregateRoot */
        $fakeAggregateRoot = DummyAggregateRoot::fake();

        $fakeAggregateRoot->given([
            new DummyEvent(1),
            new DummyEvent(2),
        ]);

        $fakeAggregateRoot->dummy();

        $fakeAggregateRoot->assertRecorded([
            new DummyEvent(3),
        ]);
    }

    /** @test */
    public function it_can_assert_that_an_event_is_not_recorded()
    {
        DummyAggregateRoot::fake()->assertNotRecorded(DummyEvent::class);

        DummyAggregateRoot::fake()->assertNotRecorded([DummyEvent::class]);
    }

    /** @test */
    public function it_can_assert_that_an_event_is_not_applied()
    {
        DummyAggregateRoot::fake()->assertNotApplied(DummyEvent::class);

        DummyAggregateRoot::fake()->assertNotApplied([DummyEvent::class]);
    }

    /** @test */
    public function it_can_assert_that_noting_is_applied()
    {
        DummyAggregateRoot::fake()->assertNothingApplied();
    }
}
