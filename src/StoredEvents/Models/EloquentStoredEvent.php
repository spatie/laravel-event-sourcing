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
        ], $this->getOriginalEvent());
    }

    public function setOriginalEvent(ShouldBeStored $event): self
    {
        $this->originalEvent = $event;

        return $this;
    }

    public function getOriginalEvent(): ?ShouldBeStored
    {
        if ($this->isDirty('meta_data') || $this->wasChanged('meta_data')) {
            $this->originalEvent?->setMetaData($this->meta_data?->toArray() ?? []);
        }

        return $this->originalEvent;
    }

    public function getEventAttribute(): ?ShouldBeStored
    {
        return ($event = $this->getOriginalEvent())
            ? $event
            : $this->originalEvent = $this->toStoredEvent()->event;
    }

    public function getMetaDataAttribute(): SchemalessAttributes
    {
        return SchemalessAttributes::createForModel($this, 'meta_data');
    }

    /**
     * @return EloquentStoredEventQueryBuilder<$this>
     */
    public function newEloquentBuilder($query): EloquentStoredEventQueryBuilder
    {
        return new EloquentStoredEventQueryBuilder($query);
    }

    /**
     * @return EloquentStoredEventCollection<$this>
     */
    public function newCollection(array $models = []): EloquentStoredEventCollection
    {
        return new EloquentStoredEventCollection($models);
    }

    public function scopeWithMetaDataAttributes(): Builder
    {
        // Legacy support for laravel-schemaless-attributes:^1.0
        if (! method_exists($this->meta_data, 'modelScope')) {
            return $this->meta_data->scopeWithSchemalessAttributes('meta_data');
        }

        return $this->meta_data->modelScope();
    }
}
