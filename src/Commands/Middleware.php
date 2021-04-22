<?php

namespace Spatie\EventSourcing\Commands;

use Closure;

interface Middleware
{
    public function handle(object $command, Closure $next): mixed;
}
