<?php

namespace Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory;

use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\EventHandlers\EventHandler;

final class TestReactorInSubdirectory implements EventHandler
{
    use ProjectsEvents;
}
