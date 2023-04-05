<?php

namespace Spatie\EventSourcing\Facades;

use Illuminate\Support\Facades\Facade;

class Dictionary extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'event-sourcing-alias-dictionary';
    }
}
