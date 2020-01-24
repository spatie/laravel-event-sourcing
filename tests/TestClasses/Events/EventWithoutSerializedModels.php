<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\ShouldBeStored;

class EventWithoutSerializedModels implements ShouldBeStored
{
    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
