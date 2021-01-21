<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class EventWithArray extends ShouldBeStored
{
    /**
     * @var \Carbon\CarbonInterface[]
     */
    public $values;

    public function __construct($values)
    {
        $this->values = $values;
    }
}
