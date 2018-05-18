<?php

namespace Spatie\EventProjector\Facades;

use Illuminate\Support\Facades\Facade;

class EventProjectionist extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'event-projector';
    }
}
