<?php

namespace Spatie\EventSourcing;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\EventHandlers\EventHandlerCollection;
use Spatie\EventSourcing\Events\EventHandlerFailedHandlingEvent;
use Spatie\EventSourcing\Events\FinishedEventReplay;
use Spatie\EventSourcing\Events\StartingEventReplay;
use Spatie\EventSourcing\Exceptions\InvalidEventHandler;
use Spatie\EventSourcing\Projectors\Projector;
use Spatie\EventSourcing\Projectors\QueuedProjector;
use Spatie\EventSourcing\Reactors\Reactor;
use Spatie\EventSourcing\Reactors\SyncReactor;

class Projectionist
{
    private EventHandlerCollection $projectors;

    private EventHandlerCollection $reactors;

    private bool $catchExceptions;

    private bool $replayChunkSize;

    private bool $isProjecting = false;

    private bool $isReplaying = false;

    public function __construct(array $config)
    {
        $this->projectors = new EventHandlerCollection();
        $this->reactors = new EventHandlerCollection();

        $this->catchExceptions = $config['catch_exceptions'];
        $this->replayChunkSize = $config['replay_chunk_size'];
    }

    public function addProjector($projector): Projectionist
    {
        if (is_string($projector)) {
            $projector = app($projector);
        }

        if (!$projector instanceof Projector) {
            throw InvalidEventHandler::notAProjector($projector);
        }

        $this->projectors->add($projector);

        return $this;
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
        return $this->projectors->all();
    }

    public function getProjector(string $name): ?Projector
    {
        return $this->projectors->all()->first(fn(Projector $projector) => $projector->getName() === $name);
    }

    public function getAsyncProjectorsFor(StoredEvent $storedEvent): Collection
    {
        return $this->projectors
            ->forEvent($storedEvent)
            ->reject(fn(Projector $projector) => $projector->shouldBeCalledImmediately())
            ->values();
    }

    public function getAsyncReactorsFor(StoredEvent $storedEvent): Collection
    {
        return $this->reactors
            ->forEvent($storedEvent)
            ->reject(fn(EventHandler $reactor) => $reactor instanceof SyncReactor)
            ->values();
    }

    public function addReactor($reactor): Projectionist
    {
        if (is_string($reactor)) {
            $reactor = app($reactor);
        }

        if (!$reactor instanceof EventHandler) {
            throw InvalidEventHandler::notAnEventHandler($reactor);
        }

        $this->reactors->add($reactor);

        return $this;
    }

    public function addReactors(array $reactors): Projectionist
    {
        foreach ($reactors as $reactor) {
            $this->addReactor($reactor);
        }

        return $this;
    }

    public function getReactors(): Collection
    {
        return $this->reactors->all();
    }

    public function getReactorsFor(StoredEvent $storedEvent): Collection
    {
        return $this->reactors->forEvent($storedEvent)->values();
    }

    public function addEventHandler($eventHandlerClass)
    {
        if (!is_string($eventHandlerClass)) {
            $eventHandlerClass = get_class($eventHandlerClass);
        }

        if (is_subclass_of($eventHandlerClass, Projector::class)) {
            $this->addProjector($eventHandlerClass);

            return;
        }

        if (is_subclass_of($eventHandlerClass, QueuedProjector::class)) {
            $this->addProjector($eventHandlerClass);

            return;
        }

        if (is_subclass_of($eventHandlerClass, EventHandler::class)) {
            $this->addReactor($eventHandlerClass);

            return;
        }

        throw InvalidEventHandler::notAnEventHandlingClassName($eventHandlerClass);
    }

    public function addEventHandlers(array $eventHandlers)
    {
        foreach ($eventHandlers as $eventHandler) {
            $this->addEventHandler($eventHandler);
        }
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $projectors = $this->projectors
            ->forEvent($storedEvent)
            ->reject(fn(Projector $projector) => $projector->shouldBeCalledImmediately());

        $this->applyStoredEventToProjectors(
            $storedEvent,
            $projectors
        );

        $reactors = $this->reactors
            ->forEvent($storedEvent)
            ->reject(fn(EventHandler $reactor) => $reactor instanceof SyncReactor);

        $this->applyStoredEventToReactors(
            $storedEvent,
            $reactors
        );
    }

    public function handleWithSyncProjectors(StoredEvent $storedEvent): void
    {
        $projectors = $this->projectors
            ->forEvent($storedEvent)
            ->filter(fn(Projector $projector) => $projector->shouldBeCalledImmediately());

        $this->applyStoredEventToProjectors($storedEvent, $projectors);
    }

    public function handleWithSyncReactors(StoredEvent $storedEvent): void
    {
        $reactors = $this->reactors
            ->forEvent($storedEvent)
            ->filter(fn(EventHandler $reactor) => $reactor instanceof SyncReactor);

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
            if (!$this->catchExceptions) {
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
    ): void
    {
        $projectors = new EventHandlerCollection($projectors);

        $this->isReplaying = true;

        if ($startingFromEventId === 0) {
            $projectors->all()->each(function (Projector $projector) {
                if (method_exists($projector, 'resetState')) {
                    $projector->resetState();
                }
            });
        }

        event(new StartingEventReplay($projectors->all()));

        $projectors->call('onStartingEventReplay');

        app(StoredEventRepository::class)->retrieveAllStartingFrom($startingFromEventId)->each(function (StoredEvent $storedEvent) use ($projectors, $onEventReplayed) {
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
