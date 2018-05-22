<?php

namespace Spatie\EventProjector;

use Illuminate\Database\Eloquent\Model;
use Spatie\EventProjector\EventSerializers\Serializer;

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
        $storedEvent->attributes['event_properties'] = app(Serializer::class)->serialize(clone $event);
        $storedEvent->save();

        return $storedEvent;
    }

    public function getEventAttribute(): ShouldBeStored
    {
        return app(Serializer::class)->deserialize(
           $this->event_class,
           $this->getOriginal('event_properties')
       );
    }
}
