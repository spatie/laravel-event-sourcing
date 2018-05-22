<?php

namespace Spatie\EventProjector\Projectors;

use Carbon\Carbon;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Models\ProjectorStatus;

trait ProjectsEvents
{
    public function getName(): string
    {
        if (isset($this->name)) {
            return $this->name;
        }

        return get_class($this);
    }

    public function rememberReceivedEvent(StoredEvent $storedEvent)
    {
        $this->getStatus()->rememberLastProcessedEvent($storedEvent);
    }

    public function hasReceivedAllPriorEvents(StoredEvent $storedEvent): bool
    {
        return $storedEvent->id === $this->getStatus()->last_processed_event_id + 1;
    }

    public function hasReceivedAllEvents(): bool
    {
        return (int)$this->getStatus()->last_processed_event_id === StoredEvent::getMaxId();
    }

    public function getLastProcessedEventId(): int
    {
        return $this->getStatus()->last_processed_event_id;
    }

    public function lastEventProcessedAt(): Carbon
    {
        return $this->getStatus()->updated_at;
    }

    public function resetStatus()
    {
        $this->getStatus()->delete();
    }

    protected function getStatus(): ProjectorStatus
    {
        return ProjectorStatus::getForProjector($this);
    }
}
