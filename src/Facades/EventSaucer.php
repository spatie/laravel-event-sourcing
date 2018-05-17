<?php

namespace Spatie\EventSourcerer\Facades;

use Illuminate\Support\Facades\Facade;

class EventSaucer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'event-sorcerer';
    }
}
