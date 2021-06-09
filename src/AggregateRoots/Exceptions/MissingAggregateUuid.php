<?php

namespace Spatie\EventSourcing\AggregateRoots\Exceptions;

use Exception;

class MissingAggregateUuid extends Exception
{
    public function __construct(string $commandClass)
    {
        parent::__construct("A command command that uses an aggregate root as its handler must specify which field should be used as the aggregate UUID by using the `#[AggregateUuid('field')]` attribute.");
    }
}
