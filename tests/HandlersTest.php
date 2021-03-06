<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Handlers;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class HandlersTest extends TestCase
{
    /** @test */
    public function test_find()
    {
        $listener = new TestListener();

        $handlers = Handlers::find(new EventA(), $listener);

        $this->assertEquals(['onX', 'onY'], $handlers->toArray());
    }

    /** @test */
    public function test_list()
    {
        $listener = new TestListener();

        $this->assertEquals([
            EventA::class => [
                'onX',
                'onY',
            ],
            EventB::class => [
                'onY',
                'onZ',
                '__invoke',
            ],
        ], Handlers::list($listener)->toArray());
    }
}

class EventA extends ShouldBeStored
{
}

class EventB extends ShouldBeStored
{
}

class TestListener
{
    public function onX(EventA $event): void
    {
    }

    protected function onY(EventA | EventB $event): void
    {
    }

    public function onZ(EventB $event): void
    {
    }

    public function __invoke(EventB $event): void
    {
    }

    protected function nothing()
    {
    }

    protected function nothingAsWell()
    {
    }
}
