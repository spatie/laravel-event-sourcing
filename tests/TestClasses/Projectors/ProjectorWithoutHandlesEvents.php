<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtractedEvent;

class ProjectorWithoutHandlesEvents implements Projector
{
    use ProjectsEvents;

    public function functionThatHandlesMoneyAdd(MoneyAddedEvent $event)
    {
        $event->account->addMoney($event->amount);
    }

    public function anotherFunctionThatHandlesMoneySubstracted(MoneySubtractedEvent $event)
    {
        $event->account->subtractMoney($event->amount);
    }

    public function functionWithoutTypeHint($parameter)
    {

    }

    public function functionWithUnreleatedClassTypeHint(Collection $test)
    {

    }
}
