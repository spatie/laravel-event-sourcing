<?php

namespace Spatie\EventSourcing\Support;

class Helpers
{
    private function __construct()
    {
    }

    public static function getEventClass(string $class): string
    {
        $map = config('event-sourcing.event_class_map', []);

        if (! empty($map) && in_array($class, $map)) {
            return array_search($class, $map, true);
        }

        return $class;
    }
}