<?php


namespace Spatie\EventSourcing\Reactors;


use Spatie\EventSourcing\EventHandlers\EventHandler;

interface Reactor extends EventHandler
{
    public function getName(): string;

    public function shouldBeCalledImmediately(): bool;
}
