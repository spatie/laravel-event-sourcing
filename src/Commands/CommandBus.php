<?php

namespace Spatie\EventSourcing\Commands;

use Illuminate\Pipeline\Pipeline;

class CommandBus
{
    private array $middlewares = [];

    public function middleware(Middleware ...$middlewares): self
    {
        $clone = clone $this;

        foreach ($middlewares as $middleware) {
            $clone->middlewares[] = $middleware;
        }

        return $clone;
    }

    public function dispatch(object $command): mixed
    {
        return (new Pipeline())
            ->through($this->middlewares)
            ->send($command)
            ->then(function (object $command) {
                return CommandHandler::for($command)->handle();
            });
    }
}
