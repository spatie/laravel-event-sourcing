<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Reactors;

use Spatie\EventSourcing\EventHandlers\Reactors\Reactor;

class CatchAllReactor extends Reactor
{
    public static array $log = [];

    public function __invoke(object $event): void
    {
        self::$log[] = $event::class;
    }
}
