<?php

namespace Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers\Subdirectory;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;

final class TestQueuedProjectorInSubdirectory implements Projector
{
    use ProjectsEvents;
}
