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
    public function handles(StoredEvent $storedEvent): bool
    {
        return Handlers::new($this)
            ->public()
            ->protected()
            ->accepts($storedEvent->event)
            ->all()
            ->isNotEmpty();
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $event = $storedEvent->event;

        Handlers::new($this)
            ->public()
            ->protected()
            ->accepts($event)
            ->all()
            ->each(function (Method $method) use ($event) {
                dd($this, $method->getName());
                return $this->{$method->getName()}($event);
            });
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
                return (class_exists($key) && isset(class_parents($key)[ShouldBeStored::class]))
                    || $key === 'object';
            })
            ->map(fn (Collection $group) => $group->map(fn (Method $method) => $method->getName())->all());
    }
}
