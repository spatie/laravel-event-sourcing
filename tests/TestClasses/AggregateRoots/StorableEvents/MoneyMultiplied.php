<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents;

use Spatie\EventSourcing\ShouldBeStored;

final class MoneyMultiplied implements ShouldBeStored
{
    public int $amount;

    public function __construct(int $amount)
    {
        $this->amount = $amount;
    }
}
