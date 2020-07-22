<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\EventHandlers\Projectors\ProjectsEvents;

class TestProjector extends Projector
{
    use ProjectsEvents;
}
