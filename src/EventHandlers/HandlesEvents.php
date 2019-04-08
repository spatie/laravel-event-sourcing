<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;

trait HandlesEvents
{
    public function handles(): array
    {
        return $this->getEventHandlingMethods()->keys()->toArray();
    }

    public function handle(StoredEvent $storedEvent)
    {
        $eventClass = $storedEvent->event_class;

        $handlerClassOrMethod = $this->getEventHandlingMethods()->get($eventClass);

        $parameters = [
            'event' => $storedEvent->event,
            'storedEvent' => $storedEvent,
            'aggregateUuid' => $storedEvent->aggregate_uuid,
        ];

        if (class_exists($handlerClassOrMethod)) {
            return app()->call([app($handlerClassOrMethod), '__invoke'], $parameters);
        }

        if (! method_exists($this, $handlerClassOrMethod)) {
            throw InvalidEventHandler::eventHandlingMethodDoesNotExist($this, $storedEvent->event, $handlerClassOrMethod);
        }

        app()->call([$this, $handlerClassOrMethod], $parameters);
    }

    public function handleException(Exception $exception): void
    {
        report($exception);
    }

    private function getEventHandlingMethods(): Collection
    {
        $handlesEvents = collect($this->handlesEvents ?? [])
            ->mapWithKeys(function (string $handlerMethod, $eventClass) {
                if (is_numeric($eventClass)) {
                    return [$handlerMethod => 'on'.ucfirst(class_basename($handlerMethod))];
                }

                return [$eventClass => $handlerMethod];
            });

        if ($this->handleEvent ?? false) {
            $handlesEvents->put($this->handleEvent, get_class($this));
        }

        return $handlesEvents;
    }
}
