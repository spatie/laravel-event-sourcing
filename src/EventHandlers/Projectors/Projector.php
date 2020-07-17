<?php

namespace Spatie\EventSourcing\EventHandlers\Projectors;

use Spatie\EventSourcing\EventHandlers\EventHandler;

abstract class Projector implements EventHandler
{
    use ProjectsEvents;
}
