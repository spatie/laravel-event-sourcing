<?php

namespace Spatie\EventSourcing\Facades;

use Illuminate\Support\Facades\Facade;

class Projectionist extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'event-sourcing';
    }
}
