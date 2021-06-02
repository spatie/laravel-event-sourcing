<?php

namespace Spatie\EventSourcing\StoredEvents;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\EventSourcing\Projectionist;

class HandleStoredEventJob implements HandleDomainEventJob, ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public StoredEvent $storedEvent,
        public array $tags
    ) {
    }

    public function handle(Projectionist $projectionist): void
    {
        $projectionist->handle($this->storedEvent);
    }

    public function tags(): array
    {
        return empty($this->tags)
            ? [$this->storedEvent->event_class]
            : $this->tags;
    }

    public static function createForEvent(StoredEvent $event, array $tags): HandleDomainEventJob
    {
        return new static($event, $tags);
    }
}
