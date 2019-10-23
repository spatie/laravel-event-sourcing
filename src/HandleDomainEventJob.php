<?php

namespace Spatie\EventSourcing;

use Illuminate\Contracts\Queue\ShouldQueue;

interface HandleDomainEventJob
{
    public function handle(Projectionist $projectionist): void;

    public function tags(): array;

    public static function createForEvent(StoredEvent $event, array $tags): self;
}
