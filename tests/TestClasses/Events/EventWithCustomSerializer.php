<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\Attributes\EventSerializer;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\Tests\TestClasses\EventSerializer\DummySerializer;

#[EventSerializer(DummySerializer::class)]
class EventWithCustomSerializer extends ShouldBeStored
{
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}
