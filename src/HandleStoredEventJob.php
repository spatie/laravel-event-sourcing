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

    public function __construct(StoredEvent $storedEvent)
    {
        $this->storedEvent = $storedEvent;
    }

    public function handle(EventProjectionist $eventProjectionist)
    {
        $eventProjectionist->handle($this->storedEvent);
    }
}