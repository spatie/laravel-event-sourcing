<?php

namespace Spatie\EventProjector\EventHandlers;

trait HandlesEvents
{
    public function handlesEvent(object $event): bool
    {
        return array_key_exists(get_class($event), $this->handlesEvents);
    }

    public function methodNameThatHandlesEvent(object $event): string
    {
        return $this->handlesEvents[get_class($event)] ?? '';
    }
}
