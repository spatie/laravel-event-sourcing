<?php

namespace Spatie\EventSourcing\StoredEvents\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEventCollection get
 */
class EloquentStoredEventQueryBuilder extends Builder
{
    public function startingFrom(int $storedEventId): self
    {
        $this->where('id', '>=', $storedEventId);

        return $this;
    }

    public function afterVersion(int $version): self
    {
        $this->where('aggregate_version', '>', $version);

        return $this;
    }

    public function whereAggregateRoot(string $uuid): self
    {
        $this->where('aggregate_uuid', $uuid);

        return $this;
    }

    public function whereEvent(string ...$eventClasses): self
    {
        $this->whereIn('event_class', $eventClasses);

        return $this;
    }

    public function lastEvent(string ...$eventClasses): ?EloquentStoredEvent
    {
        return $this
            ->unless(
                empty($eventClasses),
                fn(self $query) => $query->whereEvent(...$eventClasses)
            )
            ->orderByDesc('created_at')
            ->first();
    }
}
