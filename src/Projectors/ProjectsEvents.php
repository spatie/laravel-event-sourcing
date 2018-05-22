<?php

namespace Spatie\EventProjector\Projectors;

use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Models\StoredEvent;

trait ProjectsEvents
{
    public function hasReceivedAllPriorEvents(StoredEvent $storedEvent): bool
    {
        return $storedEvent->id === $this->getStatus()->last_processed_event_id + 1;
    }

    public function rememberReceivedEvent(StoredEvent $storedEvent)
    {
        $this->getStatus()->rememberLastProcessedEvent($storedEvent);
    }

    public function hasReceivedAllEvents(): bool
    {
        return $this->getStatus()->last_processed_event_id === StoredEvent::getMaxId();
    }

    public function getName(): string
    {
        return get_class($this);
    }

    protected function getStatus(): ProjectorStatus
    {
        return ProjectorStatus::getForProjector($this);
    }


}