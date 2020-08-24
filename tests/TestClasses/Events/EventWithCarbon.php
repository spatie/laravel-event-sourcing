<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Carbon\CarbonInterface;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class EventWithCarbon extends ShouldBeStored
{
    public CarbonInterface $value;

    public function __construct(CarbonInterface $value)
    {
        $this->value = $value;
    }
}
