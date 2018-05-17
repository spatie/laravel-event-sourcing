<?php

namespace Spatie\EventSorcerer\Console;

use Illuminate\Console\Command;

class ReplayEventsCommand extends Command
{
    protected $signature = 'event-sorcerer:replay-events';

    protected $description = 'Replay stored events';

    public function handle()
    {
        
    }
}