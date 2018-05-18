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
        return static::create([
            'event_class' => get_class($event),
            'serialized_event' => serialize(clone $event),
        ]);
    }

    public function getEventAttribute(): ShouldBeStored
    {
        return unserialize($this->serialized_event);
    }
}
