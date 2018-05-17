<?php

namespace Spatie\EventSorcerer\Console;

use Illuminate\Console\Command;
use Spatie\EventSorcerer\StoredEvent;

class ReplayEventsCommand extends Command
{
    protected $signature = 'event-sorcerer:replay-events';

    protected $description = 'Replay stored events';

    public function handle()
    {
        StoredEvent::chunk(function(StoredEvent $storedEvent) {

        });
    }
}