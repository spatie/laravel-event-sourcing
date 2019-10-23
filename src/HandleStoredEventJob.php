<?php

namespace Spatie\EventSourcing;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

final class HandleStoredEventJob implements HandleDomainEventJob, ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Spatie\EventSourcing\StoredEvent */
    public $storedEvent;

    /** @var array */
    public $tags;

    public function __construct(StoredEvent $storedEvent, array $tags)
    {
        $this->storedEvent = $storedEvent;

        $this->tags = $tags;
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
