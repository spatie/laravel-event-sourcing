<?php

namespace Spatie\EventSourcing\Commands\Exceptions;

use Exception;

class CommandFailed extends Exception
{
    public function __construct(object $command, int $tries)
    {
        parent::__construct("The command `" . $command::class. "` failed. Tried {$tries} times.");
    }
}
