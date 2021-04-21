<?php

namespace Spatie\EventSourcing\Commands;

use Illuminate\Contracts\Queue\ShouldQueue;

class CommandBus
{
    public function dispatch(object $command): mixed
    {
        if ($command instanceof ShouldQueue) {
            dispatch($command);

            return null;
        }

        return CommandHandler::for($command)->handle();
    }
}
