<?php

namespace Spatie\EventSourcing\StoredEvents;

use Spatie\EventSourcing\Projectionist;

interface HandleDomainEventJob
{
    public function handle(Projectionist $projectionist): void;

    public function tags(): array;

    public static function createForEvent(StoredEvent $event, array $tags): self;
}
