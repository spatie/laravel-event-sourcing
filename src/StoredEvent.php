<?php

namespace Spatie\EventSaucer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class StoredEvent extends Model
{
    public $guarded = [];

    public $casts = [
        'serialized_event' => 'array',
    ];

    public static function createForEvent(ShouldBeStored $event): self
    {
        return static::create([
            'event_name' => get_class($event),
            'serialized_event' => serialize(clone $event)
        ]);
    }

    public function getEventAttribute(): object
    {
        return unserialize($this->serialized_event);
    }

}