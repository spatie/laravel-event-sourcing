<?php

namespace Spatie\EventProjector\Events;

use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;

class ProjectorDidNotHandlePriorEvents
{
    /** @var \Spatie\EventProjector\Projectors\Projector */
    public $projector;

    /** @var \Spatie\EventProjector\Models\StoredEvent */
    public $storedEvent;

    public function __construct(Projector $projector, StoredEvent $storedEvent)
    {
        $this->projector = $projector;

        $this->storedEvent = $storedEvent;
    }
}
