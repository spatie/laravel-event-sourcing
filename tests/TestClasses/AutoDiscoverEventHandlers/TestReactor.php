<?php

namespace Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers;

use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\Projectors\ProjectsEvents;

final class TestReactor implements EventHandler
{
    use ProjectsEvents;
}

