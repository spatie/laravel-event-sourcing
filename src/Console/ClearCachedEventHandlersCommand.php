<?php

namespace Spatie\EventSourcing\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ClearCachedEventHandlersCommand extends Command
{
    protected $signature = 'event-sourcing:clear-event-handlers';

    protected $description = 'Clear cached event handlers';

    public function handle(Filesystem $files): void
    {
        $files->delete(config('event-sourcing.cache_path').'/event-handlers.php');

        $this->info('Cached event handlers cleared!');
    }
}
