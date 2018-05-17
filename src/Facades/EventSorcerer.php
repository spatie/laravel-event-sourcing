<?php

namespace Spatie\EventSorcerer\Facades;

use Illuminate\Support\Facades\Facade;

class EventSorcerer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'event-sorcerer';
    }
}
