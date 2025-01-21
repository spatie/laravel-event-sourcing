<?php

namespace Spatie\EventSourcing\StoredEvents\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

/**
 * @template TEloquentStoredEvent of EloquentStoredEvent
 *
 * @method \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEventCollection<EloquentStoredEvent> get()
 *
 * @extends Builder<TEloquentStoredEvent>
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
        $this->whereIn('event_class', array_map(
            fn (string $eventClass): string => StoredEvent::getEventClass($eventClass),
            $eventClasses,
        ));

        return $this;
    }

    public function wherePropertyIs(string $property, mixed $value): self
    {
        $this->whereJsonContains(column: "event_properties->{$property}", value: $value);

        return $this;
    }

    public function wherePropertyIsNot(string $property, mixed $value): self
    {
        $this->whereJsonDoesntContain(column: "event_properties->{$property}", value: $value);

        return $this;
    }

    public function lastEvent(string ...$eventClasses): ?EloquentStoredEvent
    {
        return $this
            ->unless(
                empty($eventClasses),
                fn (self $query) => $query->whereEvent(...$eventClasses)
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }
}
