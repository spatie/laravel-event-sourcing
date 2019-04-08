<?php

namespace Spatie\EventProjector;

final class EventSubscriber
{
    /** @var \Spatie\EventProjector\Projectionist */
    private $projectionist;

    /** @var array */
    private $config;

    public function __construct(Projectionist $projectionist, array $config = [])
    {
        $this->projectionist = $projectionist;

        $this->config = $config;
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
        call_user_func([config('event-projector.stored_event_model'), 'store'], $event);
    }

    private function shouldBeStored($event): bool
    {
        if (! class_exists($event)) {
            return false;
        }

        return is_subclass_of($event, ShouldBeStored::class);
    }
}
