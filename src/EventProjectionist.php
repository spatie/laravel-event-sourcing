<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;

class EventProjectionist
{
    /** @var \Illuminate\Support\Collection */
    public $projectors;

    /** @var \Illuminate\Support\Collection */
    public $reactors;

    public function __construct()
    {
        $this->projectors = collect();

        $this->reactors = collect();
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
            ->map(function ($eventHandler) {
                if (is_string($eventHandler)) {
                    $eventHandler = app($eventHandler);
                }

                return $eventHandler;
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

    protected function guardAgainstInvalidEventHandler($projector): void
    {
        if (! is_string($projector)) {
            return;
        }

        if (! class_exists($projector)) {
            throw InvalidEventHandler::doesNotExist($projector);
        }
    }
}
