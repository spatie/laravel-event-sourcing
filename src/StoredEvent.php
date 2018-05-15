<?php

namespace Spatie\EventSaucer;

use Illuminate\Database\Eloquent\Model;

class StoredEvent extends Model
{
    public $guarded = [];

    public $casts = [
        'event_properties' => 'array',
    ];

    public static function createForEvent(ShouldBeStored $event): self
    {
        return static::create([
            'event_name' => class_name($event),
            'event_properties' => $event->getEventLogProperties(),
        ]);
    }

    public function event()
    {
        return new $this->event_name(...$this->event_properties);
    }
}