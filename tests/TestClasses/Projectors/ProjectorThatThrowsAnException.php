<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Exception;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;

class ProjectorThatThrowsAnException extends BalanceProjector
{
    public function onMoneyAdded(MoneyAdded $event)
    {
        throw new Exception('Computer says no.');
    }
}
