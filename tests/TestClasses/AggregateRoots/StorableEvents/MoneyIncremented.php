<?php

namespace Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class MoneyIncremented extends ShouldBeStored implements MoneyAddedInterface
{
    public int $amount;

    public function __construct(int $amount)
    {
        $this->amount = $amount;
    }
}
