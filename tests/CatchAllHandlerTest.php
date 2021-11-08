<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\Tests\TestClasses\Events\TestEvent;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\CatchAllReactor;

class CatchAllHandlerTest extends TestCase
{
    /** @test */
    public function all_events_are_handled()
    {
        CatchAllReactor::$log = [];

        Projectionist::addReactor(CatchAllReactor::class);

        event(new TestEvent());

        $this->assertEquals([TestEvent::class], CatchAllReactor::$log);
    }
}
