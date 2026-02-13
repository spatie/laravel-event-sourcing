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

    /** @var array<string, string> */
    protected array $pendingProjectors = [];

    /** @var array<string, string> */
    protected array $pendingReactors = [];

    public function __construct(array $config)
    {
        $this->projectors = new EventHandlerCollection();
        $this->reactors = new EventHandlerCollection();

        $this->catchExceptions = $config['catch_exceptions'];
    }

    private function resolve(): void
    {
        if (empty($this->pendingProjectors) && empty($this->pendingReactors)) {
            return;
        }

        foreach ($this->pendingProjectors as $class) {
            $this->projectors->addEventHandler(app($class));
        }

        $this->pendingProjectors = [];

        foreach ($this->pendingReactors as $class) {
            $this->reactors->addEventHandler(app($class));
        }

        $this->pendingReactors = [];
    }

    public function fake(string $originalHandlerClass, string $fakeHandlerClass): void
    {
        $this
            ->removeEventHandler($originalHandlerClass)
            ->addEventHandler($fakeHandlerClass);
    }

    public function addProjector(string | Projector $projector): Projectionist
    {
        if ($projector instanceof Projector) {
            $this->projectors->addEventHandler($projector);

            return $this;
        }

        if (! is_subclass_of($projector, Projector::class)) {
            throw InvalidEventHandler::notAProjector(app($projector));
        }

        $this->pendingProjectors[$projector] = $projector;

        return $this;
    }

    public function removeProjector(string $projectorClass): Projectionist
    {
        unset($this->pendingProjectors[$projectorClass]);
        $this->projectors->remove([$projectorClass]);

        return $this;
    }

    public function allEventHandlers(): EventHandlerCollection
    {
        $this->resolve();

        return $this->projectors->merge($this->reactors);
    }

    public function withoutEventHandlers(?array $eventHandlers = null): Projectionist
    {
        if (is_null($eventHandlers)) {
            $this->pendingProjectors = [];
            $this->pendingReactors = [];
            $this->projectors = new EventHandlerCollection();
            $this->reactors = new EventHandlerCollection();

            return $this;
        }

        $eventHandlers = Arr::wrap($eventHandlers);

        foreach ($eventHandlers as $handler) {
            unset($this->pendingProjectors[$handler]);
            unset($this->pendingReactors[$handler]);
        }

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
        $this->resolve();

        return $this->projectors;
    }

    public function getProjector(string $name): ?Projector
    {
        $this->resolve();

        return $this->projectors->first(fn (Projector $projector) => $projector->getName() === $name);
    }

    public function getAsyncProjectorsFor(StoredEvent $storedEvent): Collection
    {
        $this->resolve();

        return $this->projectors
            ->forEvent($storedEvent)
            ->asyncEventHandlers($storedEvent);
    }

    public function addReactor(string|EventHandler $reactor): Projectionist
    {
        if ($reactor instanceof EventHandler) {
            $this->reactors->addEventHandler($reactor);

            return $this;
        }

        if (! is_subclass_of($reactor, EventHandler::class)) {
            throw InvalidEventHandler::notAnEventHandler(app($reactor));
        }

        $this->pendingReactors[$reactor] = $reactor;

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
        unset($this->pendingReactors[$reactorClass]);
        $this->reactors->remove([$reactorClass]);

        return $this;
    }

    public function getReactors(): Collection
    {
        $this->resolve();

        return $this->reactors;
    }

    public function getReactorsFor(StoredEvent $storedEvent): Collection
    {
        $this->resolve();

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
        $this->resolve();

        $projectors = $this->projectors
            ->forEvent($storedEvent)
            ->asyncEventHandlers($storedEvent);

        $this->applyStoredEventToProjectors(
            $storedEvent,
            $projectors
        );

        $reactors = $this->reactors
            ->forEvent($storedEvent)
            ->asyncEventHandlers($storedEvent);

        $this->applyStoredEventToReactors(
            $storedEvent,
            $reactors
        );
    }

    public function handleWithSyncEventHandlers(StoredEvent $storedEvent): void
    {
        $this->resolve();

        $projectors = $this->projectors
            ->forEvent($storedEvent)
            ->syncEventHandlers($storedEvent);

        $this->applyStoredEventToProjectors($storedEvent, $projectors);

        $reactors = $this->reactors
            ->forEvent($storedEvent)
            ->syncEventHandlers($storedEvent);

        $this->applyStoredEventToReactors($storedEvent, $reactors);
    }

    public function isProjecting(): bool
    {
        return $this->isProjecting;
    }

    private function applyStoredEventToProjectors(StoredEvent $storedEvent, Collection $projectors): void
    {
        $this->isProjecting = true;

        $projectors
            ->sortBy(fn (EventHandler $eventHandler) => $eventHandler->getWeight($storedEvent))
            ->each(function (EventHandler $projector) use ($storedEvent): void {
                $this->callEventHandler($projector, $storedEvent);
            });

        $this->isProjecting = false;
    }

    private function applyStoredEventToReactors(StoredEvent $storedEvent, Collection $reactors): void
    {
        $reactors
            ->sortBy(fn (EventHandler $eventHandler) => $eventHandler->getWeight($storedEvent))
            ->each(function (EventHandler $reactor) use ($storedEvent): void {
                $this->callEventHandler($reactor, $storedEvent);
            });
    }

    private function callEventHandler(EventHandler $eventHandler, StoredEvent $storedEvent): bool
    {
        /**
         * We "refresh" an event handler every time it's called, to ensure that its dependencies are properly re-injected.
         * The underlying problem is with tests: if we're faking an injected dependency when running tests and the handler is already resolved beforehand,
         * we won't get those faked dependencies.
         *
         * A better solution is to store event handler class names instead of an instantiated version of them in the list of handlers, which requires a larger and complex refactor.
         *
         * More info here: https://github.com/spatie/laravel-event-sourcing/discussions/181
         *
         * Note that we provided `Projectionist::fake` to counter this issue, but it turned out to be very cumbersome to use in complex examples,
         * which is why we reverted back to the original idea.
         *
         * @var \Spatie\EventSourcing\EventHandlers\EventHandler $eventHandler
         */
        $eventHandler = app($eventHandler::class);

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
        ?callable $onEventReplayed = null,
        ?string $aggregateUuid = null
    ): void {
        $events = collect($projectors->toArray())->map(fn (Projector $projector) => $projector->getEventHandlingMethods()->keys())->flatten()->toArray();
        $projectors = (new EventHandlerCollection($projectors))
            ->sortBy(fn (EventHandler $eventHandler) => $eventHandler->getWeight(null));

        $this->isReplaying = true;

        if ($startingFromEventId === 0) {
            $projectors->each(function (Projector $projector) use ($aggregateUuid) {
                if (method_exists($projector, 'resetState')) {
                    $projector->resetState($aggregateUuid);
                }
            });
        }

        event(new StartingEventReplay($projectors));

        $projectors->call('onStartingEventReplay');

        app(StoredEventRepository::class)
            ->runForAllStartingFrom($startingFromEventId, function (StoredEvent $storedEvent) use ($projectors, $onEventReplayed) {
                $this->applyStoredEventToProjectors(
                    $storedEvent,
                    $projectors->forEvent($storedEvent)
                );

                if ($onEventReplayed) {
                    $onEventReplayed($storedEvent);
                }
            }, 1000, $aggregateUuid, $events);

        $this->isReplaying = false;

        event(new FinishedEventReplay());

        $projectors->call('onFinishedEventReplay');
    }
}
