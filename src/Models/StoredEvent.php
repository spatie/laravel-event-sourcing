<?php

namespace Spatie\EventProjector\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EventProjector\EventSerializers\EventSerializer;
use Spatie\EventProjector\ShouldBeStored;

class StoredEvent extends Model
{
    public $guarded = [];

    public $casts = [
        'event_properties' => 'array',
    ];

    public static function createForEvent(ShouldBeStored $event): self
    {
        $storedEvent = new static();
        $storedEvent->event_class = get_class($event);
        $storedEvent->attributes['event_properties'] = app(EventSerializer::class)->serialize(clone $event);
        $storedEvent->save();

        return $storedEvent;
    }

    public function getEventAttribute(): ShouldBeStored
    {
        return app(EventSerializer::class)->deserialize(
           $this->event_class,
           $this->getOriginal('event_properties')
       );
    }
}
