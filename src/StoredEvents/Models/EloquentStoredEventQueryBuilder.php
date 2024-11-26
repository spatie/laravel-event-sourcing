<?php

namespace Spatie\EventSourcing\StoredEvents\Models;

use MongoDB\Laravel\Eloquent\Builder;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

/**
 * @method \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEventCollection get
 */
class EloquentStoredEventQueryBuilder extends Builder
{
    public function startingFrom(int $storedEventId): self
    {
        $this->where('_id', '>=', $storedEventId);

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
        $this->whereIn('event_class', array_map(
            fn(string $eventClass): string => StoredEvent::getEventClass($eventClass),
            $eventClasses,
        ));

        return $this;
    }

    public function wherePropertyIs(string $property, mixed $value): self
    {
        $this->where("event_properties.$property", $value);

        return $this;
    }

    public function wherePropertyIsNot(string $property, mixed $value): self
    {
        $this->where("event_properties.$property", '!=', $value);

        return $this;
    }

    public function lastEvent(string ...$eventClasses): ?StoredEvent
    {
        return $this
            ->unless(
                empty($eventClasses),
                fn(self $query) => $query->whereEvent(...$eventClasses)
            )
            ->orderBy('created_at', 'desc')
            ->orderBy('_id', 'desc')
            ->first();
    }
}
