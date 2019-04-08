<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtractedEvent;

final class ProjectorWithAssociativeAndNonAssociativeHandleEvents implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAddedEvent::class,
        MoneySubtractedEvent::class => 'onMoneySubtracted',
    ];

    public function onMoneyAddedEvent(MoneyAddedEvent $event)
    {
        $event->account->addMoney($event->amount);
    }

    public function onMoneySubtracted(MoneySubtractedEvent $event)
    {
        $event->account->subtractMoney($event->amount);
    }
}
