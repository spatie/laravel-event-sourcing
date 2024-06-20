<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class EventWithCreatedAtProperty extends ShouldBeStored
{
    public string $created_at;

    public function __construct(string $created_at)
    {
        $this->created_at = $created_at;
    }
}
