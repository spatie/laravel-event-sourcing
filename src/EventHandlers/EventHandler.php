<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;
use Spatie\EventProjector\Models\StoredEvent;
use Illuminate\Support\Collection;

interface EventHandler
{
    public function handlesEvents(): Collection;

    public function handleEvent(StoredEvent $event);

    public function handleException(Exception $exception);
}
