<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;
use Spatie\EventProjector\Models\StoredEvent;
use Illuminate\Support\Collection;

trait HandlesEvents
{
    public function handles(): Collection
    {
        return $this->getEventHandlers()->keys();
    }

    public function handle(StoredEvent $storedEvent)
    {
        $eventClass = $storedEvent->event_class;

        $handlerMethod = $this->getEventHandlers()->get($eventClass);

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

    protected function getEventHandlers(): Collection
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
