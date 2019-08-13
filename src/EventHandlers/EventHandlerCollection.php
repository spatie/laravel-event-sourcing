<?php

namespace Spatie\EventProjector\EventHandlers;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;

final class EventHandlerCollection
{
    /** @var \Illuminate\Support\Collection */
    private $eventHandlers;

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
        return $this->eventHandlers->filter(function (EventHandler $eventHandler) use ($storedEvent) {
            return in_array($storedEvent->event_class, $eventHandler->handles(), true);
        });
    }

    public function call(string $method)
    {
        $this->eventHandlers
            ->filter(function (EventHandler $eventHandler) use ($method) {
                return method_exists($eventHandler, $method);
            })
            ->each(function (EventHandler $eventHandler) use ($method) {
                return app()->call([$eventHandler, $method]);
            });
    }

    public function remove(array $eventHandlerClassNames): void
    {
        $this->eventHandlers = $this->eventHandlers
            ->reject(function (EventHandler $eventHandler) use ($eventHandlerClassNames) {
                return in_array(get_class($eventHandler), $eventHandlerClassNames);
            });
    }
}
