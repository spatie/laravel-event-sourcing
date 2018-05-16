<?php

namespace Spatie\EventSaucer\Facades;

use Illuminate\Support\Facades\Facade;

class EventSaucer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'event-saucer';
    }
}
