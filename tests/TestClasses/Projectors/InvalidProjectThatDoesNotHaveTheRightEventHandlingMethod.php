<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;

class InvalidProjectThatDoesNotHaveTheRightEventHandlingMethod implements Projector
{
    use ProjectsEvents;

    public $handlesEvents = [
        MoneyAdded::class => 'hahaThisMethodDoesNotExist',
    ];
}
