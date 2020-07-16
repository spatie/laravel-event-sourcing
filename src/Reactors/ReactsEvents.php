<?php

namespace Spatie\EventSourcing\Reactors;

use Spatie\EventSourcing\EventHandlers\HandlesEvents;

trait ReactsEvents
{
    use HandlesEvents;

    public function getName(): string
    {
        return $this->name ?? get_class($this);
    }

    public function shouldBeCalledImmediately(): bool
    {
        return $this instanceof AsyncReactor;
    }
}
