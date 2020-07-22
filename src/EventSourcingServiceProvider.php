<?php

namespace Spatie\EventSourcing;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\EventSourcing\Console\CacheEventHandlersCommand;
use Spatie\EventSourcing\Console\ClearCachedEventHandlersCommand;
use Spatie\EventSourcing\Console\ListCommand;
use Spatie\EventSourcing\Console\MakeAggregateCommand;
use Spatie\EventSourcing\Console\MakeProjectorCommand;
use Spatie\EventSourcing\Console\MakeReactorCommand;
use Spatie\EventSourcing\Console\MakeStorableEventCommand;
use Spatie\EventSourcing\Console\ReplayCommand;
use Spatie\EventSourcing\EventSerializers\EventSerializer;
use Spatie\EventSourcing\StoredEvents\EventSubscriber;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;
use Spatie\EventSourcing\Support\Composer;
use Spatie\EventSourcing\Support\DiscoverEventHandlers;

class EventSourcingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/event-sourcing.php' => config_path('event-sourcing.php'),
            ], 'config');
        }

        if (! class_exists('CreateStoredEventsTable')) {
            $this->publishes([
                __DIR__.'/../stubs/create_stored_events_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_stored_events_table.php'),
            ], 'migrations');
        }

        if (! class_exists('CreateSnapshotsTable')) {
            $this->publishes([
                __DIR__.'/../stubs/create_snapshots_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_snapshots_table.php'),
            ], 'migrations');
        }

        Event::subscribe(EventSubscriber::class);

        $this->discoverEventHandlers();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/event-sourcing.php', 'event-sourcing');

        $this->app->singleton(Projectionist::class, function () {
            $config = config('event-sourcing');

            $projectionist = new Projectionist($config);

            $projectionist
                ->addProjectors($config['projectors'] ?? [])
                ->addReactors($config['reactors'] ?? []);

            return $projectionist;
        });

        $this->app->alias(Projectionist::class, 'event-sourcing');

        $this->app->singleton(StoredEventRepository::class, config('event-sourcing.stored_event_repository'));

        $this->app->singleton(EventSubscriber::class, fn () => new EventSubscriber(config('event-sourcing.stored_event_repository')));

        $this->app
            ->when(ReplayCommand::class)
            ->needs('$storedEventModelClass')
            ->give(config('event-sourcing.stored_event_repository'));

        $this->app->bind(EventSerializer::class, config('event-sourcing.event_serializer'));

        $this->bindCommands();
    }

    private function bindCommands()
    {
        $this->app->bind('command.event-sourcing:list', ListCommand::class);
        $this->app->bind('command.event-sourcing:replay', ReplayCommand::class);
        $this->app->bind('command.event-sourcing:cache-event-handlers', CacheEventHandlersCommand::class);
        $this->app->bind('command.event-sourcing:clear-event-handlers', ClearCachedEventHandlersCommand::class);
        $this->app->bind('command.make:projector', MakeProjectorCommand::class);
        $this->app->bind('command.make:reactor', MakeReactorCommand::class);
        $this->app->bind('command.make:aggregate', MakeAggregateCommand::class);
        $this->app->bind('command.make:storable-event', MakeStorableEventCommand::class);

        $this->commands([
            'command.event-sourcing:list',
            'command.event-sourcing:replay',
            'command.event-sourcing:cache-event-handlers',
            'command.event-sourcing:clear-event-handlers',
            'command.make:projector',
            'command.make:reactor',
            'command.make:aggregate',
            'command.make:storable-event',
        ]);
    }

    private function discoverEventHandlers()
    {
        $projectionist = app(Projectionist::class);

        $cachedEventHandlers = $this->getCachedEventHandlers();

        if (! is_null($cachedEventHandlers)) {
            $projectionist->addEventHandlers($cachedEventHandlers);

            return;
        }

        (new DiscoverEventHandlers())
            ->within(config('event-sourcing.auto_discover_projectors_and_reactors'))
            ->useBasePath(base_path())
            ->ignoringFiles(Composer::getAutoloadedFiles(base_path('composer.json')))
            ->addToProjectionist($projectionist);
    }

    private function getCachedEventHandlers(): ?array
    {
        $cachedEventHandlersPath = config('event-sourcing.cache_path').'/event-handlers.php';

        if (! file_exists($cachedEventHandlersPath)) {
            return null;
        }

        return require $cachedEventHandlersPath;
    }
}
