<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory;

use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\EventHandlers\Projectors\ProjectsEvents;

class TestReactorInSubdirectory implements EventHandler
{
    use ProjectsEvents;
}
