<?php

namespace Spatie\EventSaucer;

trait StoresEvent
{
    public function getEventLogProperties(): array
    {
        return (new EventSerializer($this))->getSerializableProperties();
    }
}