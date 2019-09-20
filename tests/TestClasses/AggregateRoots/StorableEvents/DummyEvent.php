<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents;

use Spatie\EventSourcing\ShouldBeStored;

final class DummyEvent implements ShouldBeStored
{
    /** @var int */
    public $integer;

    public function __construct(int $integer)
    {
        $this->integer = $integer;
    }
}
