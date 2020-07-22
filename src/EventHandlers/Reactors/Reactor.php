<?php

namespace Spatie\EventSourcing\EventHandlers\Reactors;

use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\EventHandlers\HandlesEvents;

abstract class Reactor implements EventHandler
{
    use HandlesEvents;
}
