<?php

namespace Spatie\EventSourcing\EventHandlers\Projectors;

use Spatie\EventSourcing\EventHandlers\HandlesEvents;

trait ProjectsEvents
{
    use HandlesEvents;

    public function getName(): string
    {
        return $this->name ?? get_class($this);
    }
}
