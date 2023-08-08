<?php

namespace Spatie\EventSourcing\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Spatie\EventSourcing\EventRegistry;

class CacheStorableEventsCommand extends Command
{
    protected $signature = 'event-sourcing:cache-storable-events';

    protected $description = 'Cache all auto discovered storable events';

    public function handle(EventRegistry $eventRegistry, Filesystem $files): void
    {
        $this->info('Caching registered storable events...');

        $eventRegistry->getClassMap()
            ->pipe(function (Collection $eventClasses) use ($files) {
                $cachePath = config('event-sourcing.cache_path');

                $files->makeDirectory($cachePath, 0755, true, true);

                $files->put(
                    $cachePath.'/storable-events.php',
                    '<?php return '.var_export($eventClasses->toArray(), true).';'
                );
            });

        $this->info('All done!');
    }
}
