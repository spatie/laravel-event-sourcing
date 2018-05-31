<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Collection;
use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Events\FinishedReplayingAllEvents;
use Spatie\EventProjector\Events\StartingReplayingAllEvents;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;
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
    protected $replayChunkSize;

    public function __construct(array $config)
    {
        $this->projectors = collect();

        $this->reactors = collect();

        $this->replayChunkSize = $config['replay_chunk_size'] ?? 1000;
    }

    public function isReplayingEvents(): bool
    {
        return $this->isReplayingEvents;
    }

    public function addProjector($projector): self
    {
        $this->guardAgainstInvalidEventHandler($projector);

        if ($this->alreadyAdded('projector', $projector)) {
            return $this;
        }

        $this->projectors->push($projector);

        return $this;
    }

    public function addProjectors(array $projectors): self
    {
        collect($projectors)->each(function ($projector) {
            $this->addProjector($projector);
        });

        return $this;
    }

    public function getProjectors(): Collection
    {
        return $this->projectors;
    }

    public function getProjector(string $name): ?Projector
    {
        return $this
            ->instantiate($this->projectors)
            ->first(function (Projector $projector) use ($name) {
                return $projector->getName() === $name;
            });
    }

    public function addReactor($reactor): self
    {
        $this->guardAgainstInvalidEventHandler($reactor);

        if ($this->alreadyAdded('reactor', $reactor)) {
            return $this;
        }

        $this->reactors->push($reactor);

        return $this;
    }

    public function addReactors(array $reactors): self
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

    public function handle(StoredEvent $storedEvent)
    {
        $this
            ->callEventHandlers($this->projectors, $storedEvent)
            ->callEventHandlers($this->reactors, $storedEvent);
    }

    protected function callEventHandlers(Collection $eventHandlers, StoredEvent $storedEvent): self
    {
        $eventHandlers
            ->pipe(function (Collection $eventHandler) {
                return $this->instantiate($eventHandler);
            })
            ->filter(function (EventHandler $eventHandler) use ($storedEvent) {
                if ($eventHandler instanceof Projector) {
                    if (!$eventHandler->hasReceivedAllPriorEvents($storedEvent)) {
                        event(new ProjectorDidNotHandlePriorEvents($eventHandler, $storedEvent));

                        return false;
                    }
                }

                return true;
            })
            ->each(function (EventHandler $eventHandler) use ($storedEvent) {
                $this->callEventHandler($eventHandler, $storedEvent);

                if ($eventHandler instanceof Projector) {
                    $eventHandler->rememberReceivedEvent($storedEvent);
                }
            });

        return $this;
    }

    protected function callEventHandler(EventHandler $eventHandler, StoredEvent $storedEvent)
    {
        if (!isset($eventHandler->handlesEvents)) {
            throw InvalidEventHandler::cannotHandleEvents($eventHandler);
        }

        $event = $storedEvent->event;

        if (!$method = $eventHandler->methodNameThatHandlesEvent($event)) {
            return;
        }

        if (!method_exists($eventHandler, $method)) {
            throw InvalidEventHandler::eventHandlingMethodDoesNotExist($eventHandler, $event, $method);
        }

        app()->call([$eventHandler, $method], compact('event', 'storedEvent'));
    }

    public function replayEvents(Collection $projectors, ?int $afterStoredEventId = null, callable $onEventReplayed = null)
    {
        $this->isReplayingEvents = true;

        event(new StartingReplayingAllEvents($projectors));

        if (is_null($afterStoredEventId)) {
            $projectors = $this
                ->instantiate($projectors)
                ->each->resetStatus();

            $this->callMethod($projectors, 'onStartingReplayingAllEvents');
        }

        StoredEvent::query()
            ->after($afterStoredEventId ?? 0)
            ->chunk($this->replayChunkSize, function (Collection $storedEvents) use ($projectors, $onEventReplayed) {
                $storedEvents->each(function (StoredEvent $storedEvent) use ($projectors, $onEventReplayed) {
                    $this->callEventHandlers($projectors, $storedEvent);

                    if ($onEventReplayed) {
                        $onEventReplayed($storedEvent);
                    }
                });
            });

        $this->isReplayingEvents = false;

        event(new FinishedReplayingAllEvents());

        if (is_null($afterStoredEventId)) {
            $this->callMethod($projectors, 'onFinishedReplayingAllEvents');
        }
    }

    protected function guardAgainstInvalidEventHandler($eventHandler)
    {
        if (!is_string($eventHandler)) {
            return;
        }

        if (!class_exists($eventHandler)) {
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

    protected function callMethod(Collection $eventHandlers, string $method): self
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
