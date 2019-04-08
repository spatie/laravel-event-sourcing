<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;

final class MoneyAddedCountProjector implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAddedEvent::class => 'onMoneyAdded',
    ];

    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        $event->account->addition_count += 1;

        $event->account->save();
    }
}
