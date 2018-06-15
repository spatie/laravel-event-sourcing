<?php

namespace Spatie\EventProjector\Projectors;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\EventHandlers\HandlesEvents;
use Spatie\EventProjector\Exceptions\CouldNotResetProjector;

trait ProjectsEvents
{
    use HandlesEvents;

    public function getName(): string
    {
        if (isset($this->name)) {
            return $this->name;
        }

        return get_class($this);
    }

    public function streambased(): bool
    {
        if (! isset($this->streamBased)) {
            return false;
        }

        return $this->streamBased;
    }

    public function rememberReceivedEvent(StoredEvent $storedEvent)
    {
        $this->getStatus($storedEvent)->rememberLastProcessedEvent($storedEvent, $this);
    }

    public function hasReceivedAllPriorEvents(StoredEvent $storedEvent): bool
    {
        if (! $this->streamBased()) {
            return $storedEvent->id === $this->getStatus()->last_processed_event_id + 1;
        }

        $previousEvent = $storedEvent->previousInStream();
        $previousEventId = optional($previousEvent)->id ?? 0;

        $lastProcessedEventId = (int) $this->getStatus($storedEvent)->last_processed_event_id ?? 0;

        return $previousEventId === $lastProcessedEventId;
    }

    public function hasReceivedAllEvents(): bool
    {
        return ProjectorStatus::hasReceivedAllEvents($this);
    }

    public function getLastProcessedEventId(): int
    {
        return $this->getStatus()->last_processed_event_id ?? 0;
    }

    public function lastEventProcessedAt(): Carbon
    {
        return $this->getStatus()->updated_at;
    }

    public function reset()
    {
        if (! method_exists($this, 'resetState')) {
            throw CouldNotResetProjector::doesNotHaveResetStateMethod($this);
        }

        $this->resetState();

        $this->getAllStatuses()->each->delete();
    }

    public function shouldBeCalledImmediately(): bool
    {
        return $this instanceof ShouldBeCalledImmediately;
    }

    protected function getStatus(StoredEvent $storedEvent = null): ProjectorStatus
    {
        return ProjectorStatus::getForProjector($this, $storedEvent);
    }

    protected function getAllStatuses(): Collection
    {
        return ProjectorStatus::getAllForProjector($this);
    }
}
