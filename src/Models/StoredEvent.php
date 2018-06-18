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

        if (method_exists($event, 'getStreamName')) {
            $storedEvent->stream_name = $event->getStreamName();
        }

        if (method_exists($event, 'getStreamId')) {
            $storedEvent->stream_id = $event->getStreamId();
        }

        $storedEvent->save();

        return $storedEvent;
    }

    public static function getMaxId(): int
    {
        return DB::table((new static())->getTable())->max('id') ?? 0;
    }

    public static function last(): ?self
    {
        return static::find(self::getMaxId());
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

    public function scopePrevious(Builder $query, self $storedEvent): ?self
    {
        static::query()
            ->where('event_class', $storedEvent->event_class)
            ->latest()
            ->first();
    }

    public function previousInStream(): ?self
    {
        return static::query()
            ->where('stream_name', $this->stream_name)
            ->where('stream_id', $this->stream_id)
            ->where('id', '<', $this->id)
            ->orderBy('id', 'desc')
            ->first();
    }
}
