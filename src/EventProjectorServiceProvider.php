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
            $projectionist = app(Projectionist::class);
            $config = config('event-projector');

            return new EventSubscriber($projectionist, $config);
        });

        $this->app
            ->when(ReplayCommand::class)
            ->needs('$storedEventModelClass')
            ->give(config('event-projector.stored_event_model'));

        $this->app->bind(EventSerializer::class, config('event-projector.event_serializer'));

        $this->bindCommands();
    }

    private function bindCommands()
    {
        $this->app->bind('command.event-projector:replay', ReplayCommand::class);
        $this->app->bind('command.make:projector', MakeProjectorCommand::class);
        $this->app->bind('command.make:reactor', MakeReactorCommand::class);
        $this->app->bind('command.make:aggregate', MakeAggregateCommand::class);
        $this->app->bind('command.make:domain-event', MakeStorableEventCommand::class);

        $this->commands([
            'command.event-projector:replay',
            'command.make:projector',
            'command.make:reactor',
            'command.make:aggregate',
            'command.make:domain-event',
        ]);
    }
}
