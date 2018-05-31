<?php

namespace Spatie\EventProjector\EventHandler;

interface EventHandler
{
    public function handlesEvent(object $event);

    public function methodNameThatHandlesEvent(object $event);
}