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

    public function streamNamesToTrack(): array
    {
        return array_wrap($this->trackStream ?? []);
    }

    public function trackEventsByStreamNameAndId(): bool
    {
        return count($this->streamNamesToTrack()) === 0;
    }

    public function handlesStreamOfStoredEvent(StoredEvent $storedEvent): bool
    {
        $trackedStreamNames = $this->streamNamesToTrack();

        if ($trackedStreamNames === []) {
            return true;
        }

        $event = $storedEvent->event;

        $streamNameOfEvent = method_exists($event, 'getStreamName')
            ? $event->getStreamName()
            : 'main';

        if (in_array('*', $trackedStreamNames)) {
            return true;
        }

        return in_array($streamNameOfEvent, $trackedStreamNames);
    }

    public function rememberReceivedEvent(StoredEvent $storedEvent)
    {
        $this->getStatus($storedEvent)->rememberLastProcessedEvent($storedEvent, $this);
    }

    public function hasReceivedAllPriorEvents(StoredEvent $storedEvent): bool
    {
        // main stream
        if (! $this->trackEventsByStreamNameAndId()) {
            return $storedEvent->id === $this->getStatus()->last_processed_event_id + 1;
        }

        // TODO: add logic for account level streams

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
        return ! $this instanceof QueuedProjector;
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
