<?php

namespace Spatie\EventProjector\Models;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\EventProjector\EventSerializers\EventSerializer;
use Spatie\EventProjector\ShouldBeStored;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\SchemalessAttributes\SchemalessAttributes;

class StoredEvent extends Model
{
    public $guarded = [];

    public $timestamps = false;

    public $casts = [
        'event_properties' => 'array',
        'meta_data' => 'array',
    ];

    public static function createForEvent(ShouldBeStored $event, string $uuid = null): StoredEventData
    {
        $storedEvent = new static();
        $storedEvent->aggregate_uuid = $uuid;
        $storedEvent->event_class = static::getEventClass(get_class($event));
        $storedEvent->attributes['event_properties'] = app(EventSerializer::class)->serialize(clone $event);
        $storedEvent->meta_data = [];
        $storedEvent->created_at = Carbon::now();

        $storedEvent->save();

        return $storedEvent->toStoredEventData();
    }

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

    public function getEventClassAttribute(string $value): string
    {
        return static::getActualClassForEvent($value);
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

    protected static function getActualClassForEvent(string $class): string
    {
        return Arr::get(config('event-projector.event_class_map', []), $class, $class);
    }

    public function getMetaDataAttribute(): SchemalessAttributes
    {
        return SchemalessAttributes::createForModel($this, 'meta_data');
    }

    public function scopeWithMetaDataAttributes(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('meta_data');
    }

    public static function storeMany(array $events, string $uuid = null): void
    {
        collect($events)
            ->map(function (ShouldBeStored $domainEvent) use ($uuid) {
                $storedEvent = static::createForEvent($domainEvent, $uuid);

                return [$domainEvent, $storedEvent];
            })
            ->eachSpread(function (ShouldBeStored $event, StoredEventData $storedEvent) {
                Projectionist::handleWithSyncProjectors($storedEvent);

                if (method_exists($event, 'tags')) {
                    $tags = $event->tags();
                }

                $storedEventJob = call_user_func(
                    [config('event-projector.stored_event_job'), 'createForEvent'],
                    $storedEvent,
                    $tags ?? []
                );

                dispatch($storedEventJob->onQueue($event->queue ?? config('event-projector.queue')));
            });
    }

    public static function store(ShouldBeStored $event, string $uuid = null): void
    {
        static::storeMany([$event], $uuid);
    }

    protected static function getEventClass(string $class): string
    {
        $map = config('event-projector.event_class_map', []);

        if (!empty($map) && in_array($class, $map)) {
            return array_search($class, $map, true);
        }

        return $class;
    }
}
