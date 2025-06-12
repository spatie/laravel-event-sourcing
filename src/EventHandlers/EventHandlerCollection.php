<?php

namespace Spatie\EventSourcing\EventHandlers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

class EventHandlerCollection extends Collection
{
    public function __construct($eventHandlers = [])
    {
        parent::__construct([]);

        foreach ($eventHandlers as $eventHandler) {
            $this->addEventHandler($eventHandler);
        }
    }

    public function addEventHandler(EventHandler $eventHandler): void
    {
        $this->items[get_class($eventHandler)] = $eventHandler;
    }

    public function forEvent(StoredEvent $storedEvent): EventHandlerCollection
    {
        $eventHandlers = $this
            ->filter(
                function (EventHandler $eventHandler) use ($storedEvent) {
                    return $eventHandler->handles($storedEvent);
                }
            )
            ->toArray();

        return new static($eventHandlers);
    }

    public function call(string $method): void
    {
        $this
            ->filter(fn (EventHandler $eventHandler) => method_exists($eventHandler, $method))
            ->each(fn (EventHandler $eventHandler) => app()->call([$eventHandler, $method]));
    }

    public function remove(array $eventHandlerClassNames): void
    {
        $this->items = $this
            ->reject(
                fn (EventHandler $eventHandler) => in_array(get_class($eventHandler), $eventHandlerClassNames)
            )
            ->toArray();
    }

    public function syncEventHandlers(StoredEvent $event): self
    {
        return $this
            ->reject(
                fn (EventHandler $eventHandler) => $eventHandler instanceof ShouldQueue
            )
            ->sortBy(
                fn (EventHandler $eventHandler) => $eventHandler->getWeight($event)
            );
    }

    public function asyncEventHandlers(StoredEvent $event): self
    {
        return $this
            ->filter(
                fn (EventHandler $eventHandler) => $eventHandler instanceof ShouldQueue
            )
            ->sortBy(
                fn (EventHandler $eventHandler) => $eventHandler->getWeight($event)
            );
    }
}
