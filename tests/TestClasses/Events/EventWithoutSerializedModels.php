<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class EventWithoutSerializedModels extends ShouldBeStored
{
    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
