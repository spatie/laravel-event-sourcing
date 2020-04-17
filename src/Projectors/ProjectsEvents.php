<?php

namespace Spatie\EventSourcing\Projectors;

use Spatie\EventSourcing\EventHandlers\HandlesEvents;
use Spatie\EventSourcing\Exceptions\CouldNotResetProjector;

trait ProjectsEvents
{
    use HandlesEvents;

    public function getName(): string
    {
        return $this->name ?? get_class($this);
    }

    /** @deprecated Use reset state instead */
    public function reset(): void
    {
        if (! method_exists($this, 'resetState')) {
            throw CouldNotResetProjector::doesNotHaveResetStateMethod($this);
        }

        $this->resetState();
    }

    public function shouldBeCalledImmediately(): bool
    {
        return ! $this instanceof QueuedProjector;
    }
}
