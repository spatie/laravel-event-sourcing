<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\Streamable\MoneyAdded;

class StreambasedProjector implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
    ];

    protected $streamBased = true;

    public function onMoneyAdded(MoneyAdded $event)
    {
        $event->account->addMoney($event->amount);
    }

    public function resetState()
    {
    }
}
