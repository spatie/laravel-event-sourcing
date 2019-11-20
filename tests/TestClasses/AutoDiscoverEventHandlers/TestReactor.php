<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers;

use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\Projectors\ProjectsEvents;

final class TestReactor implements EventHandler
{
    use ProjectsEvents;
}
