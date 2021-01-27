<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class MoneyAddedCountProjector extends Projector
{
    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        $event->account->addition_count += 1;

        $event->account->save();
    }
}
