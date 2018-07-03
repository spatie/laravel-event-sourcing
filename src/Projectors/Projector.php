<?php

namespace Spatie\EventProjector\Projectors;

use Carbon\Carbon;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\EventHandlers\EventHandler;

interface Projector extends EventHandler
{
    public function getName(): string;

    public function rememberReceivedEvent(StoredEvent $storedEvent);

    public function markAsNotUpToDate(StoredEvent $storedEvent);

    public function hasAlreadyReceivedEvent(StoredEvent $storedEvent): bool;

    public function hasReceivedAllPriorEvents(StoredEvent $storedEvent): bool;

    public function hasReceivedAllEvents(): bool;

    public function getLastProcessedEventId(): int;

    public function lastEventProcessedAt(): Carbon;
}
