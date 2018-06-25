<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;

class MoneyAddedCountProjector implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
    ];

    public function onMoneyAdded(MoneyAdded $event)
    {
        $event->account->addition_count += 1;

        $event->account->save();
    }
}
