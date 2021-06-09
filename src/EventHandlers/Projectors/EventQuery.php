<?php

namespace Spatie\EventSourcing\EventHandlers\Projectors;

use Spatie\EventSourcing\EventHandlers\AppliesEvents;

abstract class EventQuery
{
    use AppliesEvents;
}
