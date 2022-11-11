<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\Tests\TestClasses\Events\TestEvent;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\CatchAllReactor;
use function PHPUnit\Framework\assertEquals;

it('should handle all events', function () {
    CatchAllReactor::$log = [];

    Projectionist::addReactor(CatchAllReactor::class);

    event(new TestEvent());

    assertEquals([TestEvent::class], CatchAllReactor::$log);
});
