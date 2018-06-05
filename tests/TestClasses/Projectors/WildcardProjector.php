<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;

class WildcardProjector implements Projector
{
    use ProjectsEvents;

    public $handlesEvents = [
        '*' => 'onAnyEvent',
    ];

    public function onAnyEvent(MoneyAdded $event)
    {
        $event->account->addMoney($event->amount);
    }
}
