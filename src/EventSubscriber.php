<?php

namespace Spatie\EventProjector;

final class EventSubscriber
{
    /** @var \Spatie\EventProjector\StoredEventRepository */
    private $repository;

    public function __construct(string $storedEventRepository)
    {
        $this->repository = app($storedEventRepository);
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
        $storedEvent = $this->repository->persist($event);
        $storedEvent->handle();
    }

    private function shouldBeStored($event): bool
    {
        if (! class_exists($event)) {
            return false;
        }

        return is_subclass_of($event, ShouldBeStored::class);
    }
}
