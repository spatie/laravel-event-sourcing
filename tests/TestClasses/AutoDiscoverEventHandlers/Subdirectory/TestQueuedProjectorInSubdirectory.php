<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory;

use Spatie\EventSourcing\Projectors\Projector;
use Spatie\EventSourcing\Projectors\ProjectsEvents;

class TestQueuedProjectorInSubdirectory implements Projector
{
    use ProjectsEvents;
}
