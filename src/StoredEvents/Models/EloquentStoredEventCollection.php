<?php

namespace Spatie\EventSourcing\StoredEvents\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class EloquentStoredEventCollection extends EloquentCollection
{
    /**
     * @return \Illuminate\Support\Collection|\Spatie\EventSourcing\StoredEvents\StoredEvent[]
     */
    public function toStoredEvents(): Collection
    {
        return $this
            ->map(
                fn(EloquentStoredEvent $eloquentStoredEvent) => $eloquentStoredEvent->toStoredEvent()
            )
            ->values();
    }
}
