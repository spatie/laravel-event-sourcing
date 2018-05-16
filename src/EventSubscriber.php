<?php

namespace Spatie\EventSaucer;

use Illuminate\Support\Collection;

class EventSubscriber
{
    /** @var \Spatie\EventSaucer\EventSaucer */
    protected $eventSaucer;

    public function __construct(EventSaucer $eventSaucer)
    {
        $this->eventSaucer = $eventSaucer;
    }

    public function subscribe($events)
    {
        $events->listen('*', static::class . '@handleEvent');
    }

    public function handleEvent(string $eventName, $payload)
    {
        if (!$this->shouldBeStored($eventName)) {
            return;
        }

        $this->storeEvent($payload[0]);
    }

    public function storeEvent(ShouldBeStored $event)
    {
        StoredEvent::createForEvent($event);

        $this
            ->callEventHandlers($this->eventSaucer->mutators, $event) // do some transaction stuff here
            ->callEventHandlers($this->eventSaucer->reactors, $event);
    }

    protected function shouldBeStored($event): bool
    {
        if (!class_exists($event)) {
            return false;
        }

        return is_subclass_of($event, ShouldBeStored::class);
    }

    protected function callEventHandlers(Collection $eventHandlers, ShouldBeStored $event): self
    {
        $eventHandlers
            ->map(function(string $eventHandlerClass) {
                return app($eventHandlerClass);
            })
            ->each(function (object $eventHandler) use ($event) {
                $this->callEventHandler($eventHandler, $event);
            });

        return $this;
    }

    protected function callEventHandler(object $eventHandler, ShouldBeStored $event)
    {
        if (! isset($eventHandler->handlesEvents)) {
            return;
        }

        if (! $method = $eventHandler->handlesEvents[get_class($event)] ?? false) {
            return;
        }

        app()->call([$eventHandler, $method], ['event' => $event]);
    }
}