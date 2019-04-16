<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\EventProjector\Console\ReplayCommand;
use Spatie\EventProjector\Console\MakeReactorCommand;
use Spatie\EventProjector\Console\MakeAggregateCommand;
use Spatie\EventProjector\Console\MakeProjectorCommand;
use Spatie\EventProjector\Console\MakeStorableEventCommand;
use Spatie\EventProjector\EventSerializers\EventSerializer;
use Spatie\EventProjector\Console\CacheEventHandlersCommand;
use Spatie\EventProjector\Console\ClearCachedEventHandlersCommand;

final class EventProjectorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/event-projector.php' => config_path('event-projector.php'),
            ], 'config');
        }

        if (! class_exists('CreateStoredEventsTable')) {
            $this->publishes([
                __DIR__.'/../stubs/create_stored_events_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_stored_events_table.php'),
            ], 'migrations');
        }

        Event::subscribe(EventSubscriber::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/event-projector.php', 'event-projector');

        $this->app->singleton(Projectionist::class, function () {
            $config = config('event-projector');

            $projectionist = new Projectionist($config);

            $projectionist
                ->addProjectors($config['projectors'] ?? [])
                ->addReactors($config['reactors'] ?? []);

            return $projectionist;
        });

        $this->app->alias(Projectionist::class, 'event-projector');

        $this->app->singleton(EventSubscriber::class, function () {
            return new EventSubscriber(config('event-projector.stored_event_model'));
        });

        $this->app
            ->when(ReplayCommand::class)
            ->needs('$storedEventModelClass')
            ->give(config('event-projector.stored_event_model'));

        $this->app->bind(EventSerializer::class, config('event-projector.event_serializer'));

        $this->bindCommands();

        $this->discoverEventHandlers();
    }

    private function bindCommands()
    {
        $this->app->bind('command.event-projector:replay', ReplayCommand::class);
        $this->app->bind('command.event-projector:cache-event-handlers', CacheEventHandlersCommand::class);
        $this->app->bind('command.event-projector:clear-event-handlers', ClearCachedEventHandlersCommand::class);
        $this->app->bind('command.make:projector', MakeProjectorCommand::class);
        $this->app->bind('command.make:reactor', MakeReactorCommand::class);
        $this->app->bind('command.make:aggregate', MakeAggregateCommand::class);
        $this->app->bind('command.make:storable-event', MakeStorableEventCommand::class);

        $this->commands([
            'command.event-projector:replay',
            'command.event-projector:cache-event-handlers',
            'command.event-projector:clear-event-handlers',
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
            ->within(config('event-projector.discover_event_handlers_in_directories'))
            ->useBasePath(app_path())
            ->addToProjectionist($projectionist);
    }

    private function getCachedEventHandlers(): ?array
    {
        $cachedEventHandlersPath = config('event-projector.cache_path');

        if (! file_exists($cachedEventHandlersPath)) {
            return null;
        }

        return require $cachedEventHandlersPath;
    }
}
