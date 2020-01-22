<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory;

use Spatie\EventSourcing\Projectors\Projector;
use Spatie\EventSourcing\Projectors\ProjectsEvents;

class TestProjectorInSubdirectory implements Projector
{
    use ProjectsEvents;
}
