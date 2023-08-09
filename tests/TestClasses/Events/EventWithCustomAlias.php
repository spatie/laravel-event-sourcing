<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class EventWithCustomAlias extends ShouldBeStored
{
    public static function eventName(string $mappedAlias = null): string
    {
        return 'event-with-alias-from-method';
    }
}
