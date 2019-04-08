<?php

namespace Spatie\EventProjector\Projectors;

use Spatie\EventProjector\EventHandlers\HandlesEvents;
use Spatie\EventProjector\Exceptions\CouldNotResetProjector;

trait ProjectsEvents
{
    use HandlesEvents;

    public function getName(): string
    {
        return $this->name ?? get_class($this);
    }

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

    protected function getStoredEventClass(): string
    {
        return config('event-projector.stored_event_model');
    }
}
