<?php

namespace Spatie\EventProjector;

use Illuminate\Database\Eloquent\Model;

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
        $storedEvent->attributes['event_properties'] = (new EventSerializer())->serialize(clone $event);
        $storedEvent->save();

       return $storedEvent;
    }

    public function getEventAttribute(): ShouldBeStored
    {
       return (new EventSerializer())->deserialize(
           $this->event_class,
           $this->getOriginal('event_properties')
       );
    }
}
