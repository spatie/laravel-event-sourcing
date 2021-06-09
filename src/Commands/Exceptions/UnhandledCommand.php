<?php

namespace Spatie\EventSourcing\Commands\Exceptions;

use Exception;

class UnhandledCommand extends Exception
{
    public function __construct(string $commandClass)
    {
        parent::__construct("No handler triggered for command {$commandClass}");
    }
}
