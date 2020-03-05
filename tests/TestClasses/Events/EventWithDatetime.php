<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use DateTimeImmutable;
use Spatie\EventSourcing\ShouldBeStored;

class EventWithDatetime extends ShouldBeStored
{
    public DateTimeImmutable $value;

    public function __construct(DateTimeImmutable $value)
    {
        $this->value = $value;
    }
}
