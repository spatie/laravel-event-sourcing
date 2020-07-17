<?php

namespace Spatie\EventSourcing\StoredEvents\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Spatie\SchemalessAttributes\SchemalessAttributes;

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
            'aggregate_uuid' => $this->aggregate_uuid ?? '',
            'aggregate_version' => $this->aggregate_version ?? 0,
            'event_class' => $this->event_class,
            'meta_data' => $this->meta_data,
            'created_at' => $this->created_at,
        ]);
    }

    public function getEventAttribute(): ?ShouldBeStored
    {
        return $this->toStoredEvent()->event;
    }

    public function getMetaDataAttribute(): SchemalessAttributes
    {
        return SchemalessAttributes::createForModel($this, 'meta_data');
    }

    public function scopeWithMetaDataAttributes(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('meta_data');
    }

    public function scopeStartingFrom(Builder $query, int $storedEventId): void
    {
        $query->where('id', '>=', $storedEventId);
    }

    public function scopeAfterVersion(Builder $query, int $version): void
    {
        $query->where('aggregate_version', '>', $version);
    }

    public function scopeUuid(Builder $query, string $uuid): void
    {
        $query->where('aggregate_uuid', $uuid);
    }
}
