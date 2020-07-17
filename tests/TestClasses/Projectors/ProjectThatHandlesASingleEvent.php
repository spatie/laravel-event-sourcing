<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class ProjectThatHandlesASingleEvent extends Projector
{
    public string $handleEvent = MoneyAddedEvent::class;

    public function __invoke(MoneyAddedEvent $event)
    {
        $event->account->addMoney($event->amount);
    }
}
