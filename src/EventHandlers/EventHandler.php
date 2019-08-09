<?php

namespace Spatie\EventProjector\EventHandlers;

use Exception;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEventData;

interface EventHandler
{
    public function handles(): array;

    public function handle(StoredEventData $event);

    public function handleException(Exception $exception): void;

    public function getEventHandlingMethods(): Collection;
}
