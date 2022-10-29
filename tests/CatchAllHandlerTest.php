<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\Tests\TestClasses\Events\TestEvent;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\CatchAllReactor;
use function PHPUnit\Framework\assertEquals;

test('all events are handled', function () {
    CatchAllReactor::$log = [];

    Projectionist::addReactor(CatchAllReactor::class);

    event(new TestEvent());

    assertEquals([TestEvent::class], CatchAllReactor::$log);
});
