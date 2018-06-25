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

    public function rememberReceivedEvent(StoredEvent $storedEvent)
    {
        $this->getStatus('main')->rememberLastProcessedEvent($storedEvent, $this);
    }

    public function hasReceivedAllPriorEvents(StoredEvent $storedEvent): bool
    {
        $lastStoredEvent = StoredEvent::query()
            ->whereIn('event_class', $this->handlesEventClassNames())
            ->where('id', '<', $storedEvent->id)
            ->orderBy('id', 'desc')
            ->first();

        $lastStoredEventId = (int) optional($lastStoredEvent)->id ?? 0;

        $lastProcessedEventId = (int) $this->getStatus()->last_processed_event_id ?? 0;

        return $lastStoredEventId === $lastProcessedEventId;


/*
        $streams = $this->groupProjectorStatusBy();





        return [
            'account_id' => $storedEvent->event->account_id,
        ];


        //$query->where('event_properties->account_id', $storedEvent->event->account_id);
        //under the hood ook where met alle event classes waar projector naar luistert

*/
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

    public function groupProjectorStatusBy(StoredEvent $storedEvent): array
    {
        return [];
    }

    protected function getStatus(string $stream = 'main'): ProjectorStatus
    {
        return ProjectorStatus::getForProjector($this, $stream);
    }

    protected function getAllStatuses(): Collection
    {
        return ProjectorStatus::getAllForProjector($this);
    }


}
