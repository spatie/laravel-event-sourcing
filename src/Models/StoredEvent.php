<?php

namespace Spatie\EventProjector\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EventProjector\EventSerializers\EventSerializer;
use Spatie\EventProjector\ShouldBeStored;

class StoredEvent extends Model
{
    public $guarded = [];

    public $timestamps = false;

    public $casts = [
        'event_properties' => 'array',
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
}
