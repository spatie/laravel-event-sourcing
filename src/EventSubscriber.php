<?php

namespace Spatie\EventProjector;

class EventSubscriber
{
    /** @var \Spatie\EventProjector\EventProjectionist */
    protected $eventProjectionist;

    /** @var array */
    protected $config;

    public function __construct(EventProjectionist $eventProjectionist, array $config)
    {
        $this->eventProjectionist = $eventProjectionist;

        $this->config = $config;
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
        $this->eventProjectionist->storeEvent($event);
    }

    protected function shouldBeStored($event): bool
    {
        if (! class_exists($event)) {
            return false;
        }

        return is_subclass_of($event, ShouldBeStored::class);
    }
}
