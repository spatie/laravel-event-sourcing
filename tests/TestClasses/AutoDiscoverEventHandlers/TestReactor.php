<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers;

use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\Projectors\ProjectsEvents;

class TestReactor implements EventHandler
{
    use ProjectsEvents;
}
