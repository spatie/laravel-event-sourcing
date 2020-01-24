<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\Projectors\Projector;
use Spatie\EventSourcing\Projectors\ProjectsEvents;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class ProjectThatHandlesASingleEvent implements Projector
{
    use ProjectsEvents;

    public string $handleEvent = MoneyAddedEvent::class;

    public function __invoke(MoneyAddedEvent $event)
    {
        $event->account->addMoney($event->amount);
    }
}
