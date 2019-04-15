<?php

namespace Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory;

use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\Projectors\ProjectsEvents;

final class TestReactorInSubdirectory implements EventHandler
{
    use ProjectsEvents;
}

