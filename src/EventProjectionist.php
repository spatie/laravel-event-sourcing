<?php

namespace Spatie\EventProjector;

use Exception;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\Events\FinishedEventReplay;
use Spatie\EventProjector\Events\StartingEventReplay;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;
use Spatie\EventProjector\Events\EventHandlerFailedHandlingEvent;
use Spatie\EventProjector\Events\ProjectorDidNotHandlePriorEvents;

class EventProjectionist
{
    /** @var \Illuminate\Support\Collection */
    protected $projectors;

    /** @var \Illuminate\Support\Collection */
    protected $reactors;

    /** @var bool */
    protected $isReplayingEvents = false;

    /** @var int */
    protected $config;

    public function __construct(array $config)
    {
        $this->projectors = collect();

        $this->reactors = collect();

        $this->config = $config;
    }

    public function isReplayingEvents(): bool
    {
        return $this->isReplayingEvents;
    }

    public function addProjector($projector): EventProjectionist
    {
        $this->guardAgainstInvalidEventHandler($projector);

        if ($this->alreadyAdded('projector', $projector)) {
            return $this;
        }

        $this->projectors->push($projector);

        return $this;
    }

    public function addProjectors(array $projectors): EventProjectionist
    {
        collect($projectors)->each(function ($projector) {
            $this->addProjector($projector);
        });

        return $this;
    }

    public function getProjectors(): Collection
    {
        return $this->instantiate($this->projectors);
    }

    public function getProjector(string $name): ?Projector
    {
        return $this
            ->instantiate($this->projectors)
            ->first(function (Projector $projector) use ($name) {
                return $projector->getName() === $name;
            });
    }

    public function addReactor($reactor): EventProjectionist
    {
        $this->guardAgainstInvalidEventHandler($reactor);

        if ($this->alreadyAdded('reactor', $reactor)) {
            return $this;
        }

        $this->reactors->push($reactor);

        return $this;
    }

    public function addReactors(array $reactors): EventProjectionist
    {
        collect($reactors)->each(function ($reactor) {
            $this->addReactor($reactor);
        });

        return $this;
    }

    public function getReactors(): Collection
    {
        return $this->reactors;
    }

    public function storeEvent(ShouldBeStored $event)
    {
        $storedEvent = $this->config['stored_event_model']::createForEvent($event);

        $this->handleImmediately($storedEvent);

        dispatch(new HandleStoredEventJob($storedEvent))->onQueue($this->config['queue']);
    }

    public function handle(StoredEvent $storedEvent)
    {
        $this
            ->callEventHandlers($this->projectors, $storedEvent)
            ->callEventHandlers($this->reactors, $storedEvent);
    }

    public function handleImmediately(StoredEvent $storedEvent)
    {
        $projectors = $this->instantiate($this->projectors);

        $projectors = $projectors->filter->shouldBeCalledImmediately();

        $this->callEventHandlers($projectors, $storedEvent);
    }

    protected function callEventHandlers(Collection $eventHandlers, StoredEvent $storedEvent): EventProjectionist
    {
        $eventHandlers
            ->pipe(function (Collection $eventHandlers) {
                return $this->instantiate($eventHandlers);
            })
            ->filter(function (EventHandler $eventHandler) use ($storedEvent) {
                if (! $method = $eventHandler->methodNameThatHandlesEvent($storedEvent->event)) {
                    return false;
                }

                if (! method_exists($eventHandler, $method)) {
                    throw InvalidEventHandler::eventHandlingMethodDoesNotExist($eventHandler, $storedEvent->event, $method);
                }

                return true;
            })
            ->filter(function (EventHandler $eventHandler) use ($storedEvent) {
                if (! $eventHandler instanceof Projector) {
                    return true;
                }

                if ($eventHandler->hasAlreadyReceivedEvent($storedEvent)) {
                    return false;
                }

                if (! $eventHandler->hasReceivedAllPriorEvents($storedEvent)) {
                    event(new ProjectorDidNotHandlePriorEvents($eventHandler, $storedEvent));

                    $eventHandler->markAsNotUpToDate($storedEvent);

                    return false;
                }

                return true;
            })
            ->each(function (EventHandler $eventHandler) use ($storedEvent) {
                $eventWasHandledSuccessfully = $this->callEventHandler($eventHandler, $storedEvent);

                if (! $eventHandler instanceof Projector) {
                    return;
                }

                if (! $eventWasHandledSuccessfully) {
                    return;
                }

                $eventHandler->rememberReceivedEvent($storedEvent);
            });

        return $this;
    }

    protected function callEventHandler(EventHandler $eventHandler, StoredEvent $storedEvent): bool
    {
        $event = $storedEvent->event;

        $method = $eventHandler->methodNameThatHandlesEvent($event);

        try {
            app()->call([$eventHandler, $method], compact('event', 'storedEvent'));
        } catch (Exception $exception) {
            if (! $this->config['catch_exceptions']) {
                throw $exception;
            }

            $eventHandler->handleException($exception);

            event(new EventHandlerFailedHandlingEvent($eventHandler, $storedEvent, $exception));

            return false;
        }

        return true;
    }

    public function replayEvents(Collection $projectors, int $afterStoredEventId = 0, callable $onEventReplayed = null)
    {
        $this->isReplayingEvents = true;

        event(new StartingEventReplay($projectors));

        $projectors = $this->instantiate($projectors);

        $this->callMethod($projectors, 'onStartingEventReplay');

        StoredEvent::query()
            ->after($afterStoredEventId ?? 0)
            ->chunk($this->config['replay_chunk_size'], function (Collection $storedEvents) use ($projectors, $onEventReplayed) {
                $storedEvents->each(function (StoredEvent $storedEvent) use ($projectors, $onEventReplayed) {
                    $this->callEventHandlers($projectors, $storedEvent);

                    if ($onEventReplayed) {
                        $onEventReplayed($storedEvent);
                    }
                });
            });

        $this->isReplayingEvents = false;

        event(new FinishedEventReplay());

        $this->callMethod($projectors, 'onFinishedEventReplay');
    }

    protected function guardAgainstInvalidEventHandler($eventHandler)
    {
        if (! is_string($eventHandler)) {
            return;
        }

        if (! class_exists($eventHandler)) {
            throw InvalidEventHandler::doesNotExist($eventHandler);
        }
    }

    protected function instantiate(Collection $eventHandlers)
    {
        return $eventHandlers->map(function ($eventHandler) {
            if (is_string($eventHandler)) {
                $eventHandler = app($eventHandler);
            }

            return $eventHandler;
        });
    }

    protected function callMethod(Collection $eventHandlers, string $method): EventProjectionist
    {
        $eventHandlers
            ->filter(function (EventHandler $eventHandler) use ($method) {
                return method_exists($eventHandler, $method);
            })
            ->each(function (EventHandler $eventHandler) use ($method) {
                return app()->call([$eventHandler, $method]);
            });

        return $this;
    }

    protected function alreadyAdded(string $type, $eventHandler)
    {
        $eventHandlerClassName = is_string($eventHandler)
            ? $eventHandler
            : get_class($eventHandler);

        $variableName = "{$type}s";

        $currentEventHandlers = $this->$variableName->toArray();

        if (in_array($eventHandlerClassName, $currentEventHandlers)) {
            return $this;
        }
    }

    protected function getClassNames(Collection $eventHandlers): array
    {
        return $eventHandlers
            ->map(function ($eventHandler) {
                if (is_string($eventHandler)) {
                    return $eventHandler;
                }

                return get_class($eventHandler);
            })
            ->toArray();
    }
}
