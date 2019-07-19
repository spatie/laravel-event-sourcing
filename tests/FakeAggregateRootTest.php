<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\StorableEvents\DummyEvent;
use Spatie\EventProjector\Tests\TestClasses\DummyAggregateRoot;

class FakeAggregateRootTest extends TestCase
{
    /** @test */
    public function it_can_determine_that_no_events_were_recorded()
    {
        DummyAggregateRoot::fake()->assertNothingRecorded();
    }

    /** @test */
    public function it_can_assert_the_recorded_events()
    {
        DummyAggregateRoot::fake()
            ->given([
                new DummyEvent(1),
                new DummyEvent(2),
            ])
            ->when(function(DummyAggregateRoot $dummyAggregateRoot) {
                $dummyAggregateRoot->dummy();
            })
            ->assertRecorded([
                new DummyEvent(3),
            ]);
    }

    /** @test */
    public function it_can_assert_recorded_events_without_using_when()
    {
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

    public function the_fake_method_can_accept_given_events()
    {
        $fakeAggregateRoot = DummyAggregateRoot::fake([
            new DummyEvent(1),
            new DummyEvent(2),
        ]);

        $fakeAggregateRoot->dummy();

        $fakeAggregateRoot->assertRecorded([
            new DummyEvent(3),
        ]);
    }
}

