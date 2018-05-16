<?php

namespace Spatie\EventSaucer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class StoredEvent extends Model
{
    public $guarded = [];

    public $casts = [
        'event_properties' => 'array',
    ];

    public static function createForEvent(ShouldBeStored $event): self
    {
        return static::create([
            'event_name' => get_class($event),
            'event_properties' => serialize($event)
        ]);
    }

    public function getEventAttribute(): object
    {
        return unserialize($this->event_properties);
    }

}