<?php

namespace Spatie\EventProjector;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class HandleStoredEventJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Spatie\EventProjector\StoredEvent */
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

    public static function createForEvent(StoredEvent $event, array $tags): self
    {
        return new static($event, $tags);
    }
}
