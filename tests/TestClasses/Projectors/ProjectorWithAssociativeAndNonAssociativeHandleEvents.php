<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;

class ProjectorWithAssociativeAndNonAssociativeHandleEvents implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAdded::class,
        MoneySubtracted::class => 'onMoneySubtracted',
    ];

    public function onMoneyAdded(MoneyAdded $event)
    {
        $event->account->addMoney($event->amount);
    }

    public function onMoneySubtracted(MoneySubtracted $event)
    {
        $event->account->subtractMoney($event->amount);
    }
}
