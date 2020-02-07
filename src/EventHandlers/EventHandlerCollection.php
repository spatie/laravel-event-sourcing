<?php

namespace Spatie\EventSourcing\EventHandlers;

use Illuminate\Support\Collection;
use Spatie\EventSourcing\StoredEvent;

class EventHandlerCollection
{
    private Collection $eventHandlers;

    public function __construct($eventHandlers = [])
    {
        $this->eventHandlers = collect();

        foreach ($eventHandlers as $eventHandler) {
            $this->add($eventHandler);
        }
    }

    public function add(EventHandler $eventHandler): void
    {
        $this->eventHandlers[get_class($eventHandler)] = $eventHandler;
    }

    public function all(): Collection
    {
        return $this->eventHandlers;
    }

    public function forEvent(StoredEvent $storedEvent): Collection
    {
        return $this->eventHandlers->filter(fn (EventHandler $eventHandler) => in_array($storedEvent->event_class, $eventHandler->handles(), true));
    }

    public function call(string $method)
    {
        $this->eventHandlers
            ->filter(fn (EventHandler $eventHandler) => method_exists($eventHandler, $method))
            ->each(fn (EventHandler $eventHandler) => app()->call([$eventHandler, $method]));
    }

    public function remove(array $eventHandlerClassNames): void
    {
        $this->eventHandlers = $this->eventHandlers
            ->reject(fn (EventHandler $eventHandler) => in_array(get_class($eventHandler), $eventHandlerClassNames));
    }
}
