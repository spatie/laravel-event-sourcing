<?php

namespace Spatie\EventProjector;

final class EventSubscriber
{
    /** @var string */
    private $storedEventModel;

    public function __construct(string $storedEventModel)
    {
        $this->storedEventModel = $storedEventModel;
    }

    public function subscribe($events): void
    {
        $events->listen('*', static::class.'@handle');
    }

    public function handle(string $eventName, $payload): void
    {
        if (! $this->shouldBeStored($eventName)) {
            return;
        }

        $this->storeEvent($payload[0]);
    }

    public function storeEvent(ShouldBeStored $event): void
    {
        $this->storedEventModel::store($event);
    }

    private function shouldBeStored($event): bool
    {
        if (! class_exists($event)) {
            return false;
        }

        return is_subclass_of($event, ShouldBeStored::class);
    }
}
