<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers;

use Spatie\EventSourcing\Projectors\ProjectsEvents;
use Spatie\EventSourcing\EventHandlers\EventHandler;

final class TestReactor implements EventHandler
{
    use ProjectsEvents;
}
