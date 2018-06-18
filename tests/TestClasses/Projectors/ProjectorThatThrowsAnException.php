<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Exception;
use Spatie\EventProjector\Projectors\QueuedProjector;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;

class ProjectorThatThrowsAnException extends BalanceProjector implements QueuedProjector
{
    public function onMoneyAdded(MoneyAdded $event)
    {
        throw new Exception('Computer says no.');
    }
}
