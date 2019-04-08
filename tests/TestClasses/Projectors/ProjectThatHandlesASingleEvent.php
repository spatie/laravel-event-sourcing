<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;

final class ProjectThatHandlesASingleEvent implements Projector
{
    use ProjectsEvents;

    public $handleEvent = MoneyAddedEvent::class;

    public function __invoke(MoneyAddedEvent $event)
    {
        $event->account->addMoney($event->amount);
    }
}
