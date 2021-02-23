<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Projectionist;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\DummyAggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\DummyEvent;

class FakeAggregateRootSideEffectsTest extends TestCase
{
    /** @test */
    public function it_will_apply_the_given_events()
    {
        $projectionist = app(Projectionist::class);

        $projectionist->addProjector(FakeAggregateRootSideEffectsProjector::class);

        DummyAggregateRoot::fake()
            ->given([
                new DummyEvent(123),
            ]);

        $this->assertFalse(FakeAggregateRootSideEffectsProjector::$triggered);
    }
}

class FakeAggregateRootSideEffectsProjector extends Projector
{
    public static $triggered = false;

    public static function clear(): void
    {
        self::$triggered = false;
    }

    public function on(DummyEvent $dummyEvent): void
    {
        self::$triggered = true;
    }
}
