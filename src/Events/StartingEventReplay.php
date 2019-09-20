<?php

namespace Spatie\EventSourcing\Events;

use Illuminate\Support\Collection;

final class StartingEventReplay
{
    /** @var \Illuminate\Support\Collection */
    public $projectors;

    public function __construct(Collection $projectors)
    {
        $this->projectors = $projectors;
    }
}
