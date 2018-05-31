<?php

namespace Spatie\EventProjector\Events;

use Illuminate\Support\Collection;

class StartingReplayingAllEvents
{
    /** @var \Illuminate\Support\Collection */
    private $projectors;

    public function __construct(Collection $projectors)
    {
        $this->projectors = $projectors;
    }
}
