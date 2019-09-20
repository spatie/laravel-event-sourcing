<?php

namespace Spatie\EventSourcing\Projectors;

use Spatie\EventSourcing\EventHandlers\EventHandler;

interface Projector extends EventHandler
{
    public function getName(): string;

    public function shouldBeCalledImmediately(): bool;
}
