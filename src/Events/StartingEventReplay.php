<?php

namespace Spatie\EventSourcing\Events;

use Illuminate\Support\Collection;

class StartingEventReplay
{
    /** @var \Illuminate\Support\Collection */
    public Collection $projectors;

    public function __construct(Collection $projectors)
    {
        $this->projectors = $projectors;
    }
}
