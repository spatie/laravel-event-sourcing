<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Events\FinishedEventReplay;
use Spatie\EventProjector\Events\StartingEventReplay;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;

class EventProjectionist
{
    /** @var \Illuminate\Support\Collection */
    public $projectors;

    /** @var \Illuminate\Support\Collection */
    public $reactors;

    /** @var bool */
    protected $isReplayingEvents = false;

    public function __construct()
    {
        $this->projectors = collect();

        $this->reactors = collect();
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

    public function registerProjectors(array $projectors): self
    {
        collect($projectors)->each(function ($projector) {
            $this->addProjector($projector);
        });

        return $this;
    }

    public function addReactor($reactor): self
    {
        $this->guardAgainstInvalidEventHandler($reactor);

        $this->reactors->push($reactor);

        return $this;
    }

    public function registerReactors(array $reactors): self
    {
        collect($reactors)->each(function ($reactor) {
            $this->addReactor($reactor);
        });

        return $this;
    }

    public function callEventHandlers(Collection $eventHandlers, ShouldBeStored $event): self
    {
        $eventHandlers
            ->pipe(function (Collection $eventHandler) {
                return $this->instanciate($eventHandler);
            })
            ->each(function (object $eventHandler) use ($event) {
                $this->callEventHandler($eventHandler, $event);
            });

        return $this;
    }

    protected function callEventHandler(object $eventHandler, ShouldBeStored $event)
    {
        if (! isset($eventHandler->handlesEvents)) {
            throw InvalidEventHandler::cannotHandleEvents($eventHandler);
        }

        if (! $method = $eventHandler->handlesEvents[get_class($event)] ?? false) {
            return;
        }

        if (! method_exists($eventHandler, $method)) {
            throw InvalidEventHandler::eventHandlingMethodDoesNotExist($eventHandler, $event, $method);
        }

        app()->call([$eventHandler, $method], compact('event'));
    }

    public function replayEvents(Collection $projectors, callable $onEventReplayed)
    {
        $projectors = $this->instanciate($projectors);

        $this->isReplayingEvents = true;

        event(new StartingEventReplay());

        $this->callMethod($projectors, 'onStartingEventReplay');

        StoredEvent::chunk(1000, function (Collection $storedEvents) use ($projectors, $onEventReplayed) {
            $storedEvents->each(function (StoredEvent $storedEvent) use ($projectors, $onEventReplayed) {
                $this->callEventHandlers($projectors, $storedEvent->event);

                $onEventReplayed($storedEvent);
            });
        });

        $this->isReplayingEvents = false;

        event(new FinishedEventReplay());

        $this->callMethod($projectors, 'onFinishedEventReplay');
    }

    protected function guardAgainstInvalidEventHandler($projector)
    {
        if (! is_string($projector)) {
            return;
        }

        if (! class_exists($projector)) {
            throw InvalidEventHandler::doesNotExist($projector);
        }
    }

    protected function instanciate(Collection $eventHandlers)
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
