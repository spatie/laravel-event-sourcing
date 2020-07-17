<?php

namespace Spatie\EventSourcing;

use Spatie\EventSourcing\StoredEvents\StoredEvent;

interface HandleDomainEventJob
{
    public function handle(Projectionist $projectionist): void;

    public function tags(): array;

    public static function createForEvent(StoredEvent $event, array $tags): self;
}
