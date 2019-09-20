<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory;

use Spatie\EventSourcing\Projectors\ProjectsEvents;
use Spatie\EventSourcing\EventHandlers\EventHandler;

final class TestReactorInSubdirectory implements EventHandler
{
    use ProjectsEvents;
}
