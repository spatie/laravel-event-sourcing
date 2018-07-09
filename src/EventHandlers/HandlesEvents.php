<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;
use Spatie\EventProjector\Models\StoredEvent;
use Illuminate\Support\Collection;

trait HandlesEvents
{
    public function handlesEvents(): Collection
    {
        return collect($this->handlesEvents ?? [])
            ->mapWithKeys(function (string $handlerMethod, $eventClass) {
                if (is_numeric($eventClass)) {
                    return [$handlerMethod => 'on'.ucfirst(class_basename($handlerMethod))];
                }

                return [$eventClass => $handlerMethod];
            });
    }

    public function handleEvent(StoredEvent $storedEvent)
    {
        $eventClass = $storedEvent->event_class;
        $handlesEvents = $this->handlesEvents();

        if (! $handlesEvents->has($eventClass)) {
            return;
        }

        $handlerMethod = $handlesEvents[$eventClass];

        if (! method_exists($this, $handlerMethod)) {
            throw InvalidEventHandler::eventHandlingMethodDoesNotExist($this, $storedEvent->event, $handlerMethod);
        }

        app()->call([$this, $handlerMethod], [
            'event' => $storedEvent->event,
            'storedEvent' => $storedEvent,
        ]);
    }

    public function handleException(Exception $exception)
    {
        report($exception);
    }
}
