<?php

namespace Spatie\EventProjector\Events;

use Exception;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;

class EventHandlerFailedHandlingEvent
{
    /** @var \Spatie\EventProjector\Projectors\Projector */
    public $projector;

    /** @var \Spatie\EventProjector\Models\StoredEvent */
    public $storedEvent;

    /** @var \Exception */
    public $exception;

    public function __construct(Projector $projector, StoredEvent $storedEvent, Exception $exception)
    {
        $this->projector = $projector;

        $this->storedEvent = $storedEvent;

        $this->exception = $exception;
    }
}
