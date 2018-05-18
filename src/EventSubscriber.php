<?php

namespace Spatie\EventProjector;

class EventSubscriber
{
    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $evenSorcerer;

    /** @var string */
    protected $storedEventModelClass;

    public function __construct(EventProjectionist $evenSorcerer, string $storedEventModelClass)
    {
        $this->evenSorcerer = $evenSorcerer;

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
        $this->storedEventModelClass::createForEvent($event);

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
