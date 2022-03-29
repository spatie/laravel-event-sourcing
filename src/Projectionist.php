<?php

namespace Spatie\EventSourcing;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\EventHandlers\EventHandlerCollection;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Events\EventHandlerFailedHandlingEvent;
use Spatie\EventSourcing\Events\FinishedEventReplay;
use Spatie\EventSourcing\Events\StartingEventReplay;
use Spatie\EventSourcing\Exceptions\InvalidEventHandler;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

class Projectionist
{
    protected EventHandlerCollection $projectors;

    protected EventHandlerCollection $reactors;

    protected bool $catchExceptions;

    protected bool $isProjecting = false;

    protected bool $isReplaying = false;

    public function __construct(array $config)
    {
        $this->projectors = new EventHandlerCollection();
        $this->reactors = new EventHandlerCollection();

        $this->catchExceptions = $config['catch_exceptions'];
    }

    public function fake(string $originalHandlerClass, string $fakeHandlerClass): void
    {
        $this
            ->removeEventHandler($originalHandlerClass)
            ->addEventHandler($fakeHandlerClass);
    }

    public function addProjector(string | Projector $projector): Projectionist
    {
        if (is_string($projector)) {
            $projector = app($projector);
        }

        if (! $projector instanceof Projector) {
            throw InvalidEventHandler::notAProjector($projector);
        }

        $this->projectors->addEventHandler($projector);

        return $this;
    }

    public function removeProjector(string $projectorClass): Projectionist
    {
        $this->projectors->remove([$projectorClass]);

        return $this;
    }

    public function allEventHandlers(): EventHandlerCollection
    {
        return $this->projectors->merge($this->reactors);
    }

    public function withoutEventHandlers(array $eventHandlers = null): Projectionist
    {
        if (is_null($eventHandlers)) {
            $this->projectors = new EventHandlerCollection();
            $this->reactors = new EventHandlerCollection();

            return $this;
        }

        $eventHandlers = Arr::wrap($eventHandlers);

        $this->projectors->remove($eventHandlers);

        $this->reactors->remove($eventHandlers);

        return $this;
    }

    public function withoutEventHandler(string $eventHandler): Projectionist
    {
        return $this->withoutEventHandlers([$eventHandler]);
    }

    public function addProjectors(array $projectors): Projectionist
    {
        foreach ($projectors as $projector) {
            $this->addProjector($projector);
        }

        return $this;
    }

    public function getProjectors(): Collection
    {
        return $this->projectors;
    }

    public function getProjector(string $name): ?Projector
    {
        return $this->projectors->first(fn (Projector $projector) => $projector->getName() === $name);
    }

    public function getAsyncProjectorsFor(StoredEvent $storedEvent): Collection
    {
        return $this->projectors
            ->forEvent($storedEvent)
            ->asyncEventHandlers();
    }

    public function addReactor($reactor): Projectionist
    {
        if (is_string($reactor)) {
            $reactor = app($reactor);
        }

        if (! $reactor instanceof EventHandler) {
            throw InvalidEventHandler::notAnEventHandler($reactor);
        }

        $this->reactors->addEventHandler($reactor);

        return $this;
    }

    public function addReactors(array $reactors): Projectionist
    {
        foreach ($reactors as $reactor) {
            $this->addReactor($reactor);
        }

        return $this;
    }

    public function removeReactor(string $reactorClass): Projectionist
    {
        $this->reactors->remove([$reactorClass]);

        return $this;
    }

    public function getReactors(): Collection
    {
        return $this->reactors;
    }

    public function getReactorsFor(StoredEvent $storedEvent): Collection
    {
        return $this->reactors->forEvent($storedEvent);
    }

    public function addEventHandler($eventHandlerClass)
    {
        if (! is_string($eventHandlerClass)) {
            $eventHandlerClass = get_class($eventHandlerClass);
        }

        if (is_subclass_of($eventHandlerClass, Projector::class)) {
            $this->addProjector($eventHandlerClass);

            return;
        }

        if (is_subclass_of($eventHandlerClass, EventHandler::class)) {
            $this->addReactor($eventHandlerClass);

            return;
        }

        throw InvalidEventHandler::notAnEventHandlingClassName($eventHandlerClass);
    }

    public function removeEventHandler(string $eventHandlerClass): self
    {
        if (is_subclass_of($eventHandlerClass, Projector::class)) {
            $this->removeProjector($eventHandlerClass);

            return $this;
        }

        if (is_subclass_of($eventHandlerClass, EventHandler::class)) {
            $this->removeReactor($eventHandlerClass);

            return $this;
        }

        throw InvalidEventHandler::notAnEventHandlingClassName($eventHandlerClass);
    }

    public function addEventHandlers(array $eventHandlers): self
    {
        foreach ($eventHandlers as $eventHandler) {
            $this->addEventHandler($eventHandler);
        }

        return $this;
    }

    /**
     * @param array|Collection|LazyCollection $events
     */
    public function handleStoredEvents($events): void
    {
        collect($events)
            ->each(fn (StoredEvent $storedEvent) => $this->handleWithSyncEventHandlers($storedEvent))
            ->each(fn (StoredEvent $storedEvent) => $this->handle($storedEvent));
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $projectors = $this->projectors
            ->forEvent($storedEvent)
            ->asyncEventHandlers();

        $this->applyStoredEventToProjectors(
            $storedEvent,
            $projectors
        );

        $reactors = $this->reactors
            ->forEvent($storedEvent)
            ->asyncEventHandlers();

        $this->applyStoredEventToReactors(
            $storedEvent,
            $reactors
        );
    }

    public function handleWithSyncEventHandlers(StoredEvent $storedEvent): void
    {
        $projectors = $this->projectors
            ->forEvent($storedEvent)
            ->syncEventHandlers();

        $this->applyStoredEventToProjectors($storedEvent, $projectors);

        $reactors = $this->reactors
            ->forEvent($storedEvent)
            ->syncEventHandlers();

        $this->applyStoredEventToReactors($storedEvent, $reactors);
    }

    public function isProjecting(): bool
    {
        return $this->isProjecting;
    }

    private function applyStoredEventToProjectors(StoredEvent $storedEvent, Collection $projectors): void
    {
        $this->isProjecting = true;

        foreach ($projectors as $projector) {
            $this->callEventHandler($projector, $storedEvent);
        }

        $this->isProjecting = false;
    }

    private function applyStoredEventToReactors(StoredEvent $storedEvent, Collection $reactors): void
    {
        foreach ($reactors as $reactor) {
            $this->callEventHandler($reactor, $storedEvent);
        }
    }

    private function callEventHandler(EventHandler $eventHandler, StoredEvent $storedEvent): bool
    {
        try {
            $eventHandler->handle($storedEvent);
        } catch (Exception $exception) {
            if (! $this->catchExceptions) {
                throw $exception;
            }

            $eventHandler->handleException($exception);

            event(new EventHandlerFailedHandlingEvent($eventHandler, $storedEvent, $exception));

            return false;
        }

        return true;
    }

    public function isReplaying(): bool
    {
        return $this->isReplaying;
    }

    public function replay(
        Collection $projectors,
        int $startingFromEventId = 0,
        callable $onEventReplayed = null
    ): void {
        $projectors = new EventHandlerCollection($projectors);

        $this->isReplaying = true;

        if ($startingFromEventId === 0) {
            $projectors->each(function (Projector $projector) {
                if (method_exists($projector, 'resetState')) {
                    $projector->resetState();
                }
            });
        }

        event(new StartingEventReplay($projectors));

        $projectors->call('onStartingEventReplay');

        app(StoredEventRepository::class)
            ->retrieveAllStartingFrom($startingFromEventId)
            ->each(function (StoredEvent $storedEvent) use ($projectors, $onEventReplayed) {
                $this->applyStoredEventToProjectors(
                    $storedEvent,
                    $projectors->forEvent($storedEvent)
                );

                if ($onEventReplayed) {
                    $onEventReplayed($storedEvent);
                }
            });

        $this->isReplaying = false;

        event(new FinishedEventReplay());

        $projectors->call('onFinishedEventReplay');
    }
}
