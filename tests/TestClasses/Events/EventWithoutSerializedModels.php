<?php

namespace Spatie\EventProjector\Tests\TestClasses\Events;

use Spatie\EventProjector\ShouldBeStored;

class EventWithoutSerializedModels implements ShouldBeStored
{
    /** @var string */
    public $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
