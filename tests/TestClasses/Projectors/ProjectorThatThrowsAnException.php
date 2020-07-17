<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class ProjectorThatThrowsAnException extends BalanceProjector implements ShouldQueue
{
    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        throw new Exception('Computer says no.');
    }
}
