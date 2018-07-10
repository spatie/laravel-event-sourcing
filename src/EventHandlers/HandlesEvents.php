<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;

trait HandlesEvents
{
    public function handles(): Collection
    {
        return $this->getEventHandlingMethods()->keys();
    }

    public function handle(StoredEvent $storedEvent)
    {
        $eventClass = $storedEvent->event_class;

        $handlerMethod = $this->getEventHandlingMethods()->get($eventClass);

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

    protected function getEventHandlingMethods(): Collection
    {
        return collect($this->handlesEvents ?? [])
            ->mapWithKeys(function (string $handlerMethod, $eventClass) {
                if (is_numeric($eventClass)) {
                    return [$handlerMethod => 'on'.ucfirst(class_basename($handlerMethod))];
                }

                return [$eventClass => $handlerMethod];
            });
    }
}
