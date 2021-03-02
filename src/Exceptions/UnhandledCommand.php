<?php

namespace Spatie\EventSourcing\Exceptions;

use Exception;
use Throwable;

class UnhandledCommand extends Exception
{
    public function __construct(string $commandClass)
    {
        parent::__construct("No handler triggered for command {$commandClass}");
    }
}
