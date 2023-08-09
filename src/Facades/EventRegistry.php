<?php

namespace Spatie\EventSourcing\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Spatie\EventSourcing\EventRegistry as Registry;

/**
 * @method static Registry addEventClass(string $eventClass, string $alias = null)
 * @method static Registry addEventClasses(array $eventClasses)
 * @method static string getEventClass(string $alias)
 * @method static string getAlias(string $eventClass)
 * @method static Registry setClassMap(array $classMap)
 * @method static Collection getClassMap()
 *
 * @see \Spatie\EventSourcing\EventRegistry
 */
class EventRegistry extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'event-registry';
    }
}
