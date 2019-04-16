<?php

namespace Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers;

use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\EventHandlers\EventHandler;

final class TestReactor implements EventHandler
{
    use ProjectsEvents;
}
