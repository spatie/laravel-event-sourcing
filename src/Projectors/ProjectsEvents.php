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
        $streamFullNames = $this->getEventStreams($storedEvent)
            ->map(function ($streamValue, $streamName) {
                return "{$streamName}-{$streamValue}";
            })
            ->toArray();

        if (count($streamFullNames) === 0) {
            $streamFullNames = ['main'];
        }

        foreach ($streamFullNames as $streamName) {
            $this->getStatus($streamName)->rememberLastProcessedEvent($storedEvent, $this);
        }
    }

    public function hasReceivedAllPriorEvents(StoredEvent $storedEvent): bool
    {
        $streams = $this->getEventStreams($storedEvent);

        if ($streams->isEmpty()) {
            $lastStoredEvent = StoredEvent::query()
                ->whereIn('event_class', $this->handlesEventClassNames())
                ->where('id', '<', $storedEvent->id)
                ->orderBy('id', 'desc')
                ->first();

            $lastStoredEventId = (int) optional($lastStoredEvent)->id ?? 0;

            $status = $this->getStatus();

            $lastProcessedEventId = (int) $status->last_processed_event_id ?? 0;

            if ($lastStoredEventId !== $lastProcessedEventId) {
                $status->has_received_all_prior_events = false;
                $status->save();

                return false;
            }

            $status->has_received_all_prior_events = true;
            $status->save();
        }

        foreach ($streams as $streamName => $streamValue) {
            $streamFullName = "{$streamName}-{$streamValue}";
            $whereJsonClause = str_replace('.', '->', $streamName);

            $lastStoredEvent = StoredEvent::query()
                ->whereIn('event_class', $this->handlesEventClassNames())
                ->where('id', '<', $storedEvent->id)
                ->where("event_properties->{$whereJsonClause}", $streamValue)
                ->orderBy('id', 'desc')
                ->first();

            $lastStoredEventId = (int) optional($lastStoredEvent)->id ?? 0;

            $lastProcessedEventId = (int) $this->getStatus($streamFullName)->last_processed_event_id ?? 0;

            if ($lastStoredEventId !== $lastProcessedEventId) {
                return false;
            }
        }

        return true;
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

    protected function getEventStreams(StoredEvent $storedEvent): Collection
    {
        $streams = method_exists($this, 'streamEventsBy')
            ? $this->streamEventsBy($storedEvent)
            : [];

        return collect(array_wrap($streams))
            ->mapWithKeys(function ($streamValue, $streamName) use ($storedEvent) {
                if (is_numeric($streamName)) {
                    $streamName = $streamValue;

                    $streamValue = array_get($storedEvent->event_properties, $streamName);
                }

                return [$streamName => $streamValue];
            });
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
