<?php

namespace Spatie\EventSourcing\StoredEvents\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * @template TEloquentStoredEvent of EloquentStoredEvent
 *
 * @extends EloquentCollection<array-key, TEloquentStoredEvent>
 */
class EloquentStoredEventCollection extends EloquentCollection
{
    /**
     * @return \Illuminate\Support\Collection|\Spatie\EventSourcing\StoredEvents\StoredEvent[]
     */
    public function toStoredEvents(): Collection
    {
        return $this
            ->map(
                fn (EloquentStoredEvent $eloquentStoredEvent) => $eloquentStoredEvent->toStoredEvent()
            )
            ->values();
    }
}
