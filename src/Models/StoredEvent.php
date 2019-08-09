<?php

namespace Spatie\EventProjector\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\EventProjector\ShouldBeStored;

class StoredEvent extends Model
{
    public $guarded = [];

    public $timestamps = false;

    public $casts = [
        'event_properties' => 'array',
        'meta_data' => 'array',
    ];

    public function toStoredEventData(): StoredEventData
    {
        return new StoredEventData([
            'id' => $this->id,
            'event_properties' => $this->event_properties,
            'aggregate_uuid' => $this->aggregate_uuid,
            'event_class' => $this->event_class,
            'meta_data' => $this->meta_data,
            'created_at' => $this->created_at,
        ]);
    }

    public function getEventAttribute(): ShouldBeStored
    {
        return $this->toStoredEventData()->event;
    }

    public function scopeStartingFrom(Builder $query, int $storedEventId): void
    {
        $query->where('id', '>=', $storedEventId);
    }

    public function scopeUuid(Builder $query, string $uuid): void
    {
        $query->where('aggregate_uuid', $uuid);
    }
}
