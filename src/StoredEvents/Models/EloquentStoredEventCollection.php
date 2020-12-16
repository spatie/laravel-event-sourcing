<?php

namespace Spatie\EventSourcing\StoredEvents\Models;

use Illuminate\Database\Eloquent\Collection;

class EloquentStoredEventCollection extends Collection
{
    /**
     * @return \Illuminate\Support\Collection|\Spatie\EventSourcing\StoredEvents\StoredEvent[]
     */
    public function toStoredEvents(): \Illuminate\Support\Collection
    {
        return $this->map(fn (EloquentStoredEvent $eloquentStoredEvent) => $eloquentStoredEvent->toStoredEvent())->values();
    }
}
