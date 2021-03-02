<?php

namespace Spatie\EventSourcing\Exceptions;

use Exception;

class CommandHandlerNotFound extends Exception
{
    public function __construct(string $commandClass)
    {
        parent::__construct("No handler found for command {$commandClass}");
    }
}
