<?php

namespace Spatie\EventSaucer;

class EventSubscriber
{
    public $mutators = [

    ];

    public $actions = [

    ];

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

        collect(EventSaucer::$mutators)
            ->each(function (string $mutatorClass) use ($event) {
                app()->call($mutatorClass . '@handle', [$event]);
            });

        collect(EventSaucer::$reactors)
            ->each(function (string $reactionClass) use ($event) {
                app()->call($reactionClass . '@handle', [$event]);
            });
    }


    protected function shouldBeStored($event): bool
    {

        if (!class_exists($event)) {
            return false;
        }

        return is_subclass_of($event, ShouldBeStored::class);
    }

    public function subscribe($events)
    {
        $events->listen('*', static::class . '@handleEvent');
    }
}