<?php

namespace Spatie\EventProjector;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\EventProjector\Models\StoredEvent;

class HandleStoredEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Spatie\EventProjector\Models\StoredEvent */
    public $storedEvent;

    /** @var array */
    public $tags;

    public function __construct(StoredEvent $storedEvent, array $tags)
    {
        $this->storedEvent = $storedEvent;
        $this->tags = $tags;
    }

    public function handle(Projectionist $projectionist)
    {
        $projectionist->handle($this->storedEvent);
    }

    public function tags(): array
    {
        if (empty($this->tags)) {
            return [
                $this->storedEvent['event_class'],
            ];
        }

        return $this->tags;
    }
}
