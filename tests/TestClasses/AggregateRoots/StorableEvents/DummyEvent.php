<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents;

use Spatie\EventSourcing\ShouldBeStored;

final class DummyEvent implements ShouldBeStored
{
    public int $integer;

    public function __construct(int $integer)
    {
        $this->integer = $integer;
    }
}
