<?php

namespace Spatie\EventSaucer;

use Illuminate\Queue\SerializesModels;

trait StoresEvent
{
    use SerializesModels;

    public function getEventLogProperties(): array
    {
        return (new EventSerializer($this))->getSerializableProperties();
    }
}