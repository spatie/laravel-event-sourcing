<?php

namespace Spatie\EventProjector\Projectors;

use Spatie\EventProjector\EventHandlers\EventHandler;

interface Projector extends EventHandler
{
    public function getName(): string;

    public function shouldBeCalledImmediately(): bool;
}
