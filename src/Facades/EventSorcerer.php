<?php

namespace Spatie\EventProjector\Facades;

use Illuminate\Support\Facades\Facade;

class EventProjector extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'event-projector';
    }
}
