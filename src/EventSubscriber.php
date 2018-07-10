<?php

namespace Spatie\EventProjector;

class EventSubscriber
{
    /** @var \Spatie\EventProjector\Projectionist */
    protected $projectionist;

    /** @var array */
    protected $config;

    public function __construct(Projectionist $projectionist, array $config)
    {
        $this->projectionist = $projectionist;

        $this->config = $config;
    }

    public function subscribe($events)
    {
        $events->listen('*', static::class.'@handle');
    }

    public function handle(string $eventName, $payload)
    {
        if (! $this->shouldBeStored($eventName)) {
            return;
        }

        $this->storeEvent($payload[0]);
    }

    public function storeEvent(ShouldBeStored $event)
    {
        $this->projectionist->storeEvent($event);
    }

    protected function shouldBeStored($event): bool
    {
        if (! class_exists($event)) {
            return false;
        }

        return is_subclass_of($event, ShouldBeStored::class);
    }
}
