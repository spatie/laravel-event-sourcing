<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;

trait HandlesEvents
{
    public function handlesEvents(): array
    {
        return collect($this->handlesEvents ?? [])
            ->mapWithKeys(function ($methodName, $eventClass) {
                if (is_numeric($eventClass)) {
                    $eventClass = $methodName;
                    $methodName = 'on'.ucfirst(class_basename($eventClass));
                }

                return [$eventClass => $methodName];
            })
            ->toArray();
    }

    public function handlesEventClassNames(): array
    {
        return array_keys($this->handlesEvents());
    }

    public function methodNameThatHandlesEvent(object $event): string
    {
        $handlesEvents = $this->handlesEvents();

        $eventClass = get_class($event);

        return $handlesEvents[$eventClass] ?? '';
    }

    public function handleException(Exception $exception)
    {
        report($exception);
    }
}
