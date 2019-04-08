<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;
use Spatie\EventProjector\Models\StoredEvent;

interface EventHandler
{
    public function handles(): array;

    public function handle(StoredEvent $event);

    public function handleException(Exception $exception): void;
}
