<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Exception;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;

class ProjectorThatThrowsAnException implements Projector
{
    use ProjectsEvents;

    public $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
    ];

    public function onMoneyAdded(MoneyAdded $event)
    {
        throw new Exception('Computer says no.');
    }
}
