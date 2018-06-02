<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;

interface EventHandler
{
    public function handlesEvent(object $event);

    public function methodNameThatHandlesEvent(object $event);

    public function handleException(Exception $exception);
}
