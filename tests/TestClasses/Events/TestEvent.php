<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class TestEvent extends ShouldBeStored
{
    public function __construct()
    {
    }
}
