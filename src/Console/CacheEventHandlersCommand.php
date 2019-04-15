<?php

namespace Spatie\EventProjector\Console;

use Illuminate\Console\Command;
use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\Projectionist;

final class CacheEventHandlersCommand extends Command
{
    protected $signature = 'event-projector:cache-event-handlers';

    protected $description = 'Cache all auto discovered event handlers';

    public function handle(Projectionist $projectionist): void
    {
        $this->info('Caching registered event handlers...');

        $projectionist->getProjectors()
            ->merge($projectionist->getReactors())
            ->map(function(EventHandler $eventHandler) {
                return get_class($eventHandler);
            })
            ->pipe(function(array $eventHandlerClasses) {
                file_put_contents(
                    config('event-projector.cache_path'),
                    '<?php return '.var_export($eventHandlerClasses, true).';'
                );
            });

        $this->info('All done!');
    }
}

