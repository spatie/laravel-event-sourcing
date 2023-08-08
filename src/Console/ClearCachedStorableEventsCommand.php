<?php

namespace Spatie\EventSourcing\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ClearCachedStorableEventsCommand extends Command
{
    protected $signature = 'event-sourcing:clear-storable-events';

    protected $description = 'Clear cached storable events';

    public function handle(Filesystem $files): void
    {
        $files->delete(config('event-sourcing.cache_path').'/storable-events.php');

        $this->info('Cached storable events cleared!');
    }
}
