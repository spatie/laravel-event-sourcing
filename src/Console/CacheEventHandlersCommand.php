<?php

namespace Spatie\EventSourcing\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Spatie\EventSourcing\EventHandlers\EventHandler;
use Spatie\EventSourcing\Projectionist;

class CacheEventHandlersCommand extends Command
{
    protected $signature = 'event-sourcing:cache-event-handlers';

    protected $description = 'Cache all auto discovered event handlers';

    public function handle(Projectionist $projectionist, Filesystem $files): void
    {
        $this->info('Caching registered event handlers...');

        $projectionist->allEventHandlers()
            ->toBase()
            ->map(fn (EventHandler $eventHandler) => get_class($eventHandler))
            ->pipe(function (Collection $eventHandlerClasses) use ($files) {
                $cachePath = config('event-sourcing.cache_path');

                $files->makeDirectory($cachePath, 0755, true, true);

                $files->put(
                    $cachePath.'/event-handlers.php',
                    '<?php return '.var_export($eventHandlerClasses->toArray(), true).';'
                );
            });

        $this->info('All done!');
    }
}
