<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;

trait HandlesEvents
{
    public function handlesEvents(): array
    {
        return $this->handlesEvents ?? [];
    }

    public function handlesEvent(object $event): bool
    {
        $handlesEvents = $this->handlesEvents();

        return array_key_exists(get_class($event), $handlesEvents)
            || isset($handlesEvents);
    }

    public function methodNameThatHandlesEvent(object $event): string
    {
        $handlesEvents = $this->handlesEvents();
        $eventClass = get_class($event);

        $methodName = $this->getAssociativeMethodName($handlesEvents, $eventClass);

        if ($methodName === '') {
            $methodName = $this->getNonAssociativeMethodName($handlesEvents, $eventClass);
        }

        return $methodName;
    }

    public function handleException(Exception $exception)
    {
        report($exception);
    }

    private function getAssociativeMethodName(array $handlesEvents, string $eventClass)
    {
        $methodName = $handlesEvents[$eventClass] ?? '';

        if ($methodName !== '') {
            return $methodName;
        }

        $wildcardMethod = $handlesEvents['*'] ?? '';

        if ($wildcardMethod !== '') {
            return $wildcardMethod;
        }

        return '';
    }

    private function getNonAssociativeMethodName(array $handlesEvents, string $eventClass)
    {
        if (isset(array_flip($handlesEvents)[$eventClass])) {
            return 'on'.ucfirst(class_basename($eventClass));
        }

        return '';
    }
}
