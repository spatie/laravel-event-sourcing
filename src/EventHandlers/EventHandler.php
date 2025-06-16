<?php

namespace Spatie\EventSourcing\EventHandlers;

use Exception;
use Illuminate\Support\Collection;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

interface EventHandler
{
    public function handles(StoredEvent $storedEvent): bool;

    public function handle(StoredEvent $storedEvent): void;

    public function handleException(Exception $exception): void;

    public function getEventHandlingMethods(): Collection;

    public function getWeight(?StoredEvent $event): int;
}
