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
    /** @var array<string, string[]> */
    private static array $eventHandlingMethodNames = [];

    public function handles(StoredEvent $storedEvent): bool
    {
        return $this->getEventHandlingMethodNamesFor($storedEvent->event) !== [];
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $event = $storedEvent->event;

        collect($this->getEventHandlingMethodNamesFor($event))
            ->each(fn (string $methodName) => $this->{$methodName}($event));
    }

    /**
     * Which methods accept an event only depends on the class of the handler and the class of the event,
     * so the reflection is done once per combination instead of for every stored event.
     *
     * @return string[]
     */
    protected function getEventHandlingMethodNamesFor(?ShouldBeStored $event): array
    {
        if ($event === null) {
            return [];
        }

        return self::$eventHandlingMethodNames[static::class.'|'.$event::class] ??= Handlers::new($this)
            ->public()
            ->protected()
            ->reject(fn (Method $method) => $method->accepts(null))
            ->accepts($event)
            ->all()
            ->map(fn (Method $method) => $method->getName())
            ->values()
            ->all();
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
            ->reject(fn (Method $method) => $method->accepts(null))
            ->all()
            ->groupBy(fn (Method $method) => explode('|', (string) $method->getTypes()->first()?->getName()))
            ->filter(function (Collection $group, string $key) {
                return (class_exists($key) && isset(class_parents($key)[ShouldBeStored::class]))
                    || $key === 'object';
            })
            ->map(fn (Collection $group) => $group->map(fn (Method $method) => $method->getName())->all());
    }

    public function getWeight(?StoredEvent $event): int
    {
        return $this->weight ?? 0;
    }
}
