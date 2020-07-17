<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class InvalidProjectorThatDoesNotHaveTheRightEventHandlingMethod extends Projector
{
    protected array $handlesEvents = [
        MoneyAddedEvent::class => 'hahaThisMethodDoesNotExist',
    ];
}
