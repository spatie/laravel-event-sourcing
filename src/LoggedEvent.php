<?php

namespace Spatie\EventSaucer;

use Illuminate\Database\Eloquent\Model;

class LoggedEvent extends Model
{
    public $guarded = [];

    public $casts = [
        'event_properties' => 'array',
    ];

    public static function createForEvent(string $eventName, $event): self
    {
        return static::create([
            'event_name' => $eventName,
            'event_properties' => $event->getEventLogProperties(),
        ]);
    }

    public function getEvent()
    {
        // return
    }
}