<?php

namespace Spatie\EventSourcing\Tests\TestClasses\Events;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class EventWithDocblock extends ShouldBeStored
{
    /**
     * @var \Carbon\CarbonInterface;
     */
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
