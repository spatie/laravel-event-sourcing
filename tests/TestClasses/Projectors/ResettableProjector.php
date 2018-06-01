<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;

class ResettableProjector implements Projector
{
    use ProjectsEvents;

    public function resetState()
    {

    }
}