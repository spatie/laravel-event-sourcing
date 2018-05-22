<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;

class InvalidProjectorThatCannotHandleEvents implements Projector
{
    use ProjectsEvents;
}
