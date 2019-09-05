<?php

namespace Spatie\EventProjector\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\EventProjector\ShouldBeStored;
use Spatie\EventProjector\StoredEvent;

class EloquentStoredEvent extends Model
{
    public $guarded = [];

    public $timestamps = false;

    protected $table = 'stored_events';

    public $casts = [
        'event_properties' => 'array',
        'meta_data' => 'array',
    ];

    public function toStoredEvent(): StoredEvent
    {
        return new StoredEvent([
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
        return $this->toStoredEvent()->event;
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
