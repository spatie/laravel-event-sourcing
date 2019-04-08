<?php

namespace Spatie\EventProjector\Facades;

use Illuminate\Support\Facades\Facade;

final class Projectionist extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'event-projector';
    }
}
