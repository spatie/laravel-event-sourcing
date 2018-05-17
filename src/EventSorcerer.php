<?php

namespace Spatie\EventSorcerer;

use Illuminate\Support\Collection;
use Spatie\EventSorcerer\Exceptions\InvalidEventHandler;

class EventSorcerer
{
    /** @var \Illuminate\Support\Collection */
    public $mutators;

    /** @var \Illuminate\Support\Collection */
    public $reactors;

    public function __construct()
    {
        $this->mutators = collect();

        $this->reactors = collect();
    }

    public function addMutator(string $mutator): self
    {
        if (! class_exists($mutator)) {
            throw InvalidEventHandler::doesNotExist($mutator);
        }

        $this->mutators->push($mutator);

        return $this;
    }

    public function registerMutators(array $mutators): self
    {
        collect($mutators)->each(function ($mutator) {
            $this->addMutator($mutator);
        });

        return $this;
    }

    public function addReactor(string $reactor): self
    {
        if (! class_exists($reactor)) {
            throw InvalidEventHandler::doesNotExist($reactor);
        }

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
            ->map(function (string $eventHandlerClass) {
                return app($eventHandlerClass);
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
}
