<?php

namespace Spatie\EventProjector;

class EventSubscriber
{
    /** @var \Spatie\EventProjector\Projectionist */
    protected $Projectionist;

    /** @var array */
    protected $config;

    public function __construct(Projectionist $Projectionist, array $config)
    {
        $this->Projectionist = $Projectionist;

        $this->config = $config;
    }

    public function subscribe($events)
    {
        $events->listen('*', static::class.'@handleEvent');
    }

    public function handleEvent(string $eventName, $payload)
    {
        if (! $this->shouldBeStored($eventName)) {
            return;
        }

        $this->storeEvent($payload[0]);
    }

    public function storeEvent(ShouldBeStored $event)
    {
        $this->Projectionist->storeEvent($event);
    }

    protected function shouldBeStored($event): bool
    {
        if (! class_exists($event)) {
            return false;
        }

        return is_subclass_of($event, ShouldBeStored::class);
    }
}
