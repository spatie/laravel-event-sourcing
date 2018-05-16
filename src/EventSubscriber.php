<?php

namespace Spatie\EventSaucer;

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

        $this->eventSaucer->mutators
            ->map(function(string $mutatorClass) {
                return app($mutatorClass);
            })
            ->each(function (object $mutatorClass) use ($event) {
                $this->callEventHandler($mutatorClass, $event);
            });

        $this->eventSaucer->reactors
            ->map(function(string $reactorClass) {
                return app($reactorClass);
            })
            ->each(function (object $reactorClass) use ($event) {
                $this->callEventHandler($reactorClass, $event);
            });
    }

    protected function shouldBeStored($event): bool
    {
        if (!class_exists($event)) {
            return false;
        }

        return is_subclass_of($event, ShouldBeStored::class);
    }

    protected function callEventHandler(object $eventHandler, ShouldBeStored $event)
    {
        if (! isset($eventHandler->handlesEvents)) {
            return;
        }

        if (! $method = $eventHandler->handlesEvents[get_class($event)]) {
            return;
        }

        app()->call([$eventHandler, $method], ['event' => $event]);
    }
}