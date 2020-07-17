<?php

namespace Spatie\EventSourcing\EventHandlers;

use Exception;
use Illuminate\Support\Collection;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

interface EventHandler
{
    public function handles(): array;

    public function handle(StoredEvent $event);

    public function handleException(Exception $exception): void;

    public function getEventHandlingMethods(): Collection;
}
