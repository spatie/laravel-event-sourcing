<?php

namespace Spatie\EventProjector;

class EventSubscriber
{
    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $evenSorcerer;

    public function __construct(EventProjectionist $evenSorcerer)
    {
        $this->evenSorcerer = $evenSorcerer;
    }

    public function subscribe($events)
    {
        $events->listen('*', static::class.'@handleEvent');
    }

    public function handleEvent(string $eventName, $payload)
    {
        if (! $this->shouldBeStored($eventName)) {
            return;
        }

        $this->storeEvent($payload[0]);
    }

    public function storeEvent(ShouldBeStored $event)
    {
        StoredEvent::createForEvent($event);

        $this->evenSorcerer
            ->callEventHandlers($this->evenSorcerer->projectors, $event)
            ->callEventHandlers($this->evenSorcerer->reactors, $event);
    }

    protected function shouldBeStored($event): bool
    {
        if (! class_exists($event)) {
            return false;
        }

        return is_subclass_of($event, ShouldBeStored::class);
    }
}
