<?php

namespace Spatie\EventProjector\Projectors;

use Carbon\Carbon;
use Spatie\EventProjector\Models\StoredEvent;

interface Projector
{
    public function hasReceivedAllPriorEvents(StoredEvent $storedEvent): bool;

    public function hasReceivedAllEvents(): bool;

    public function rememberReceivedEvent(StoredEvent $storedEvent);

    public function getName(): string;

    public function getLastProcessedEventId(): int;

    public function lastEventProcessedAt(): Carbon;
}
