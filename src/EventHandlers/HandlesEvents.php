<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;

trait HandlesEvents
{
    public function handlesEvent(object $event): bool
    {
        return array_key_exists(get_class($event), $this->handlesEvents);
    }

    public function methodNameThatHandlesEvent(object $event): string
    {
        $methodName =  $this->handlesEvents[get_class($event)] ?? '';

        if ($methodName !== '') {
            return $methodName;
        }

        $wildcardMethod = $this->handlesEvents['*'] ?? '';

        if ($wildcardMethod !== '') {
           return $wildcardMethod;
        }

        return '';
    }

    public function handleException(Exception $exception)
    {
        report($exception);
    }
}
