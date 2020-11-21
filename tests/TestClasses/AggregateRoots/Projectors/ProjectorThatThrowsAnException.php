<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors;

use Exception;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;

class ProjectorThatThrowsAnException extends AccountProjector
{
    public function onMoneyAdded(MoneyAdded $event, string $aggregateUuid)
    {
        throw new Exception('Computer says no.');
    }
}
