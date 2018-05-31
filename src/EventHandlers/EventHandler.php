<?php

namespace Spatie\EventProjector\EventHandlers;

interface EventHandler
{
    public function handlesEvent(object $event);

    public function methodNameThatHandlesEvent(object $event);
}