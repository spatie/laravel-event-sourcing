<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots\StorableEvents;

use Spatie\EventProjector\ShouldBeStored;

final class DummyEvent implements ShouldBeStored
{
    /** @var int */
    public $integer;

    public function __construct(int $integer)
    {
        $this->integer = $integer;
    }
}
