<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class ClearCachedEventHandlersCommand extends Command
{
    protected $signature = 'event-projector:clear-event-handlers';

    protected $description = 'Clear cached event handlers';

    public function handle(Filesystem $files): void
    {
        $files->delete(config('event-projector.cache_path').'/event-handlers.php');

        $this->info('Cached event handlers cleared!');
    }
}
