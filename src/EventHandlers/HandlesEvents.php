<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;

trait HandlesEvents
{
    public function handlesEvents(): array
    {
        return $this->handlesEvents ?? [];
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

    protected function checkNonAssociativeEvent(array $handlesEvents, string $eventClass): bool
    {
        return array_key_exists($eventClass, array_flip($handlesEvents));
    }

    protected function getAssociativeMethodName(array $handlesEvents, string $eventClass): string
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

    protected function getNonAssociativeMethodName(array $handlesEvents, string $eventClass): string
    {
        if ($this->checkNonAssociativeEvent($handlesEvents, $eventClass)) {
            return 'on'.ucfirst(class_basename($eventClass));
        }

        return '';
    }
}
