<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Events\FinishedEventReplay;
use Spatie\EventProjector\Events\StartingEventReplay;
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
            ->filter(function (object $eventHandler) use ($storedEvent) {
                if ($eventHandler instanceof Projector) {
                    if (! $eventHandler->hasReceivedAllPriorEvents($storedEvent)) {
                        event(new ProjectorDidNotHandlePriorEvents($eventHandler, $storedEvent));

                        return false;
                    }
                }

                return true;
            })
            ->each(function (object $eventHandler) use ($storedEvent) {
                $this->callEventHandler($eventHandler, $storedEvent);

                if ($eventHandler instanceof Projector) {
                    $eventHandler->rememberReceivedEvent($storedEvent);
                }
            });

        return $this;
    }

    protected function callEventHandler(object $eventHandler, StoredEvent $storedEvent)
    {
        if (! isset($eventHandler->handlesEvents)) {
            throw InvalidEventHandler::cannotHandleEvents($eventHandler);
        }

        $event = $storedEvent->event;

        if (! $method = $eventHandler->handlesEvents[get_class($event)] ?? false) {
            return;
        }

        if (! method_exists($eventHandler, $method)) {
            throw InvalidEventHandler::eventHandlingMethodDoesNotExist($eventHandler, $event, $method);
        }

        app()->call([$eventHandler, $method], compact('event', 'storedEvent'));
    }

    public function replayEvents(Collection $projectors, callable $onEventReplayed)
    {
        $this->isReplayingEvents = true;

        event(new StartingEventReplay());

        $projectors = $this
            ->instantiate($projectors)
            ->each->resetStatus();

        $this->callMethod($projectors, 'onStartingEventReplay');

        StoredEvent::chunk($this->replayChunkSize, function (Collection $storedEvents) use ($projectors, $onEventReplayed) {
            $storedEvents->each(function (StoredEvent $storedEvent) use ($projectors, $onEventReplayed) {
                $this->callEventHandlers($projectors, $storedEvent);

                $onEventReplayed($storedEvent);
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

    protected function callMethod(Collection $eventHandlers, string $method): self
    {
        $eventHandlers
            ->filter(function (object $eventHandler) use ($method) {
                return method_exists($eventHandler, $method);
            })
            ->each(function (object $eventHandler) use ($method) {
                return app()->call([$eventHandler, $method]);
            });

        return $this;
    }
}
