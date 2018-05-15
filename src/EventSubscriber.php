<?php

namespace Spatie\EventSaucer;

class EventSubscriber
{
    public function handleEvent(string $eventName, $payload = null)
    {
        if (! $this->shouldBeLogged($eventName)) {
            return;
        }

        LoggedEvent::createForEvent($eventName, $payload[0]);

        // mutators

        // reactors
    }

    public function subscribe($events)
    {
        $events->listen('*', static::class . '@handleEvent');
    }

    protected function shouldBeLogged($event): bool
    {
        if (! class_exists($event)) {
            return false;
        }

        return in_array(LogsEvent::class, trait_uses_recursive($event));
    }
}