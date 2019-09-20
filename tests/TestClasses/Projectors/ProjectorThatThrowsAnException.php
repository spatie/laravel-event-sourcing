<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Exception;
use Spatie\EventSourcing\Projectors\QueuedProjector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class ProjectorThatThrowsAnException extends BalanceProjector implements QueuedProjector
{
    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        throw new Exception('Computer says no.');
    }
}
