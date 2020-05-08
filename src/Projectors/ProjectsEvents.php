<?php

namespace Spatie\EventSourcing\Projectors;

use Spatie\EventSourcing\EventHandlers\HandlesEvents;

trait ProjectsEvents
{
    use HandlesEvents;

    public function getName(): string
    {
        return $this->name ?? get_class($this);
    }

    public function shouldBeCalledImmediately(): bool
    {
        return ! $this instanceof QueuedProjector;
    }
}
