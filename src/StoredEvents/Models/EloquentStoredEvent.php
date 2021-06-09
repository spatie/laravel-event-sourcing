<?php

namespace Spatie\EventSourcing\StoredEvents\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Spatie\SchemalessAttributes\SchemalessAttributes;

/**
 * @method static self|\Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEventQueryBuilder|\Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEventQueryBuilder query()
 * @property-read \Spatie\SchemalessAttributes\SchemalessAttributes $meta_data
 */
class EloquentStoredEvent extends Model
{
    public $guarded = [];

    public $timestamps = false;

    protected $table = 'stored_events';

    public $casts = [
        'event_properties' => 'array',
        'meta_data' => 'array',
    ];

    protected ?ShouldBeStored $originalEvent = null;

    public function toStoredEvent(): StoredEvent
    {
        return new StoredEvent([
            'id' => $this->id,
            'event_properties' => $this->event_properties,
            'aggregate_uuid' => $this->aggregate_uuid ?? '',
            'aggregate_version' => $this->aggregate_version ?? 0,
            'event_version' => $this->event_version,
            'event_class' => $this->event_class,
            'meta_data' => $this->meta_data,
            'created_at' => $this->created_at,
        ], $this->originalEvent);
    }

    public function setOriginalEvent(ShouldBeStored $event): self
    {
        $this->originalEvent = $event;

        return $this;
    }

    public function getEventAttribute(): ?ShouldBeStored
    {
        return $this->originalEvent ??= $this->toStoredEvent()->event;
    }

    public function getMetaDataAttribute(): SchemalessAttributes
    {
        return SchemalessAttributes::createForModel($this, 'meta_data');
    }

    public function newEloquentBuilder($query): EloquentStoredEventQueryBuilder
    {
        return new EloquentStoredEventQueryBuilder($query);
    }

    public function newCollection(array $models = []): EloquentStoredEventCollection
    {
        return new EloquentStoredEventCollection($models);
    }

    public function scopeWithMetaDataAttributes(): Builder
    {
        return $this->meta_data->modelScope();
    }
}
