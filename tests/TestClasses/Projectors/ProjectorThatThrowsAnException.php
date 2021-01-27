<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Projectors;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;

class ProjectorThatThrowsAnException extends BalanceProjector implements ShouldQueue
{
    public static int $exceptionsHandled = 0;

    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        throw new Exception('Computer says no.');
    }

    public function handleException(Exception $exception): void
    {
        self::$exceptionsHandled += 1;
    }
}
