<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Reactors;

use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class CatchAllReactor extends Reactor
{
    public static array $log = [];

    public function __invoke(ShouldBeStored $event): void
    {
        self::$log[] = $event::class;
    }
}
