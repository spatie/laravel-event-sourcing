<?php

namespace Spatie\EventSourcing\EventHandlers;

use Exception;
use Illuminate\Support\Collection;
use Spatie\BetterTypes\Handlers;
use Spatie\BetterTypes\Method;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

trait HandlesEvents
{
    public function handles(): array
    {
        return $this->getEventHandlingMethods()->keys()->toArray();
    }

    public function handle(StoredEvent $storedEvent)
    {
        $eventClass = $storedEvent->event_class;

        $handlersForEvent = $this->getEventHandlingMethods()->get($eventClass);

        foreach ($handlersForEvent as $handler) {
            $this->{$handler}($storedEvent->event);
        }
    }

    public function handleException(Exception $exception): void
    {
        report($exception);
    }

    public function getEventHandlingMethods(): Collection
    {
        return Handlers::new($this)
            ->public()
            ->protected()
            ->all()
            ->groupBy(fn (Method $method) => $method->getTypes()->first()?->getName())
            ->filter(function (Collection $group, string $key) {
                return class_exists($key) && isset(class_parents($key)[ShouldBeStored::class]);
            })
            ->map(fn (Collection $group) => $group->map(fn (Method $method) => $method->getName())->all());
    }
}
