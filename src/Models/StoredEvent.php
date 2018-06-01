<?php

namespace Spatie\EventProjector\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\EventProjector\ShouldBeStored;
use Spatie\SchemalessAttributes\SchemalessAttributes;
use Spatie\EventProjector\EventSerializers\EventSerializer;

class StoredEvent extends Model
{
    public $guarded = [];

    public $timestamps = false;

    public $casts = [
        'event_properties' => 'array',
        'meta_data' => 'array',
    ];

    public static function createForEvent(ShouldBeStored $event): self
    {
        $storedEvent = new static();
        $storedEvent->event_class = get_class($event);
        $storedEvent->attributes['event_properties'] = app(EventSerializer::class)->serialize(clone $event);
        $storedEvent->created_at = now();
        $storedEvent->save();

        return $storedEvent;
    }

    public static function getMaxId(): int
    {
        return DB::table((new static())->getTable())->max('id') ?? 0;
    }

    public function getEventAttribute(): ShouldBeStored
    {
        return app(EventSerializer::class)->deserialize(
           $this->event_class,
           $this->getOriginal('event_properties')
       );
    }

    public function getMetaDataAttribute(): SchemalessAttributes
    {
        return SchemalessAttributes::createForModel($this, 'meta_data');
    }

    public function scopeWithMetaDataAttributes(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('meta_data');
    }

    public function scopeAfter(Builder $query, int $storedEventId)
    {
        $query->where('id', '>', $storedEventId);
    }
}
