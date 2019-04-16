<?php

namespace Spatie\EventProjector\Tests\TestClasses\AutoDiscoverEventHandlers;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;

final class TestProjector implements Projector
{
    use ProjectsEvents;
}
