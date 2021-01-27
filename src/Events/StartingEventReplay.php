<?php

namespace Spatie\EventSourcing\Events;

use Illuminate\Support\Collection;

class StartingEventReplay
{
    public function __construct(
        public Collection $projectors
    ) {
    }
}
