<?php

namespace Spatie\EventProjector;

class EventSubscriber
{
    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    /** @var string */
    protected $storedEventModelClass;

    public function __construct(EventProjectionist $eventProjectionist, string $storedEventModelClass)
    {
        $this->eventProjectionist = $eventProjectionist;

        $this->storedEventModelClass = $storedEventModelClass;
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
        $storedEvent = $this->storedEventModelClass::createForEvent($event);

        $this->eventProjectionist
            ->callEventHandlers($this->eventProjectionist->projectors, $storedEvent)
            ->callEventHandlers($this->eventProjectionist->reactors, $storedEvent);
    }

    protected function shouldBeStored($event): bool
    {
        if (! class_exists($event)) {
            return false;
        }

        return is_subclass_of($event, ShouldBeStored::class);
    }
}
