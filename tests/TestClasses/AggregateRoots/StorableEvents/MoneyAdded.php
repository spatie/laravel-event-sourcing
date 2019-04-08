<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots\StorableEvents;

use Spatie\EventProjector\ShouldBeStored;

final class MoneyAdded implements ShouldBeStored
{
    /** @var int */
    public $amount;

    public function __construct(int $amount)
    {
        $this->amount = $amount;
    }
}
