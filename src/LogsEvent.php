<?php

namespace Spatie\EventSaucer;

trait LogsEvent
{
    public function getEventLogProperties(): array
    {
        return (new EventSerializer($this))->getSerializableProperties();
    }
}