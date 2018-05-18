<?php

namespace Spatie\EventProjector;

use Illuminate\Database\Eloquent\Model;

class StoredEvent extends Model
{
    public $guarded = [];

    public $casts = [
        'serialized_event' => 'array',
    ];

    public static function createForEvent(ShouldBeStored $event): self
    {
        $storedEvent = new StoredEvent();
        $storedEvent->event_class = get_class($event);
        $storedEvent->attributes['serialized_event'] = (new EventSerializer())->serialize(clone $event);
        $storedEvent->save();

       return $storedEvent;
    }

    public function getEventAttribute(): ShouldBeStored
    {
       return (new EventSerializer())->deserialize($this->event_class, $this->getOriginal('serialized_event'));


    }
}
