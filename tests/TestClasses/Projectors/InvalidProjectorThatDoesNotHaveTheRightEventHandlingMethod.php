<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Projectors\Projector;
use Spatie\EventSourcing\Projectors\ProjectsEvents;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class InvalidProjectorThatDoesNotHaveTheRightEventHandlingMethod implements Projector
{
    use ProjectsEvents;

    protected array $handlesEvents = [
        MoneyAddedEvent::class => 'hahaThisMethodDoesNotExist',
    ];
}
