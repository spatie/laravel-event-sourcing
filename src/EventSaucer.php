<?php

namespace Spatie\EventSaucer;

class EventSaucer
{
    /** @var array */
    public static $mutators = [];

    public static $reactors = [];

    public static function registerMutators(array $mutators)
    {
        static::$mutators = $mutators;
    }

    public static function reactors(array $reactors)
    {
        static::$reactors = $reactors;
    }
}