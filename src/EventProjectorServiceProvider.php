<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\EventProjector\Console\ListCommand;
use Spatie\EventProjector\Console\MakeReactorCommand;
use Spatie\EventProjector\Console\ReplayEventsCommand;
use Spatie\EventProjector\Console\MakeProjectorCommand;
use Spatie\EventProjector\Console\MakeStorableEventCommand;
use Spatie\EventProjector\EventSerializers\EventSerializer;

class EventProjectorServiceProvider extends ServiceProvider
{
    public function boot()
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

        if (! class_exists('CreateProjectorStatusesTable')) {
            $this->publishes([
                __DIR__.'/../stubs/create_projector_statuses_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_projector_statuses_table.php'),
            ], 'migrations');
        }

        $this->app->bind(EventSerializer::class, config('event-projector.event_serializer'));

        $this->app->bind('command.event-projector:list', ListCommand::class);
        $this->app->bind('command.event-projector:replay-events', ReplayEventsCommand::class);
        $this->app->bind('command.make:projector', MakeProjectorCommand::class);
        $this->app->bind('command.make:reactor', MakeReactorCommand::class);
        $this->app->bind('command.make:storable-event', MakeStorableEventCommand::class);

        $this->commands([
            'command.event-projector:list',
            'command.event-projector:replay-events',
            'command.make:projector',
            'command.make:reactor',
            'command.make:storable-event',
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/event-projector.php', 'event-projector');

        $this->app->singleton(EventProjectionist::class, function () {
            $config = config('event-projector');

            $projectionist = new EventProjectionist($config);

            $projectionist->addProjectors($config['projectors'] ?? []);

            $projectionist->addReactors($config['reactors'] ?? []);

            return $projectionist;
        });

        $this->app->alias(EventProjectionist::class, 'event-projector');

        $this->app
            ->when(EventSubscriber::class)
            ->needs('$storedEventModelClass')
            ->give(config('event-projector.stored_event_model'));

        $this->app
            ->when(ReplayEventsCommand::class)
            ->needs('$storedEventModelClass')
            ->give(config('event-projector.stored_event_model'));

        Event::subscribe(EventSubscriber::class);
    }
}
