<?php

namespace Spatie\EventSourcing\Commands\Middleware;

use Closure;
use Spatie\EventSourcing\AggregateRoots\Exceptions\CouldNotPersistAggregate;
use Spatie\EventSourcing\Commands\Exceptions\CommandFailed;
use Spatie\EventSourcing\Commands\Middleware;

class RetryMiddleware implements Middleware
{
    private int $currentTries = 0;

    public function __construct(
        private int $maximumTries = 3
    ) {
    }

    public function handle(object $command, Closure $next): mixed
    {
        try {
            $this->currentTries += 1;

            return $next($command);
        } catch (CouldNotPersistAggregate) {
            if ($this->currentTries >= $this->maximumTries) {
                throw new CommandFailed($command, $this->currentTries);
            }

            return $this->handle($command, $next);
        }
    }
}
