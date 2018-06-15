<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Spatie\EventProjector\Console\ListCommand;
use Spatie\EventProjector\Console\ResetCommand;
use Spatie\EventProjector\Console\ReplayCommand;
use Spatie\EventProjector\Console\RebuildCommand;
use Spatie\EventProjector\Snapshots\SnapshotFactory;
use Spatie\EventProjector\Snapshots\SnapshotRepository;
use Spatie\EventProjector\Console\Make\MakeReactorCommand;
use Spatie\EventProjector\EventSerializers\EventSerializer;
use Spatie\EventProjector\Console\Make\MakeProjectorCommand;
use Spatie\EventProjector\Console\Make\MakeStorableEventCommand;
use Spatie\EventProjector\Console\Snapshots\ListSnapshotsCommand;
use Spatie\EventProjector\Console\Snapshots\CreateSnapshotCommand;
use Spatie\EventProjector\Console\Snapshots\DeleteSnapshotCommand;
use Spatie\EventProjector\Console\Snapshots\RestoreSnapshotCommand;

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

        Event::subscribe(EventSubscriber::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/event-projector.php', 'event-projector');

        $this->app->singleton(EventProjectionist::class, function () {
            $config = config('event-projector');

            $projectionist = new EventProjectionist($config);

            $projectionist
                ->addProjectors($config['projectors'] ?? [])
                ->addReactors($config['reactors'] ?? []);

            return $projectionist;
        });

        $this->app->alias(EventProjectionist::class, 'event-projector');

        $this->app->bind(SnapshotFactory::class, function () {
            $eventProjectionist = app(EventProjectionist::class);

            $config = config('event-projector');

            $diskName = $config['snapshots_disk'];
            $disk = Storage::disk($diskName);

            return new SnapshotFactory($eventProjectionist, $disk, $config);
        });

        $this->app->bind(SnapshotRepository::class, function () {
            $eventProjectionist = app(EventProjectionist::class);

            $config = config('event-projector');

            $diskName = $config['snapshots_disk'];
            $disk = Storage::disk($diskName);

            return new SnapshotRepository($eventProjectionist, $disk, $config);
        });

        $this->app->singleton(EventSubscriber::class, function () {
            $eventProjectionist = app(EventProjectionist::class);
            $config = config('event-projector');

            return new EventSubscriber($eventProjectionist, $config);
        });

        $this->app
            ->when(ReplayCommand::class)
            ->needs('$storedEventModelClass')
            ->give(config('event-projector.stored_event_model'));

        $this->app->bind(EventSerializer::class, config('event-projector.event_serializer'));

        $this->bindCommands();
    }

    protected function bindCommands()
    {
        $this->app->bind('command.event-projector:list', ListCommand::class);
        $this->app->bind('command.event-projector:reset', ResetCommand::class);
        $this->app->bind('command.event-projector:rebuild', RebuildCommand::class);
        $this->app->bind('command.event-projector:replay', ReplayCommand::class);

        $this->app->bind('command.event-projector:list-snapshots', ListSnapshotsCommand::class);
        $this->app->bind('command.event-projector:create-snapshot', CreateSnapshotCommand::class);
        $this->app->bind('command.event-projector:restore-snapshot', RestoreSnapshotCommand::class);
        $this->app->bind('command.event-projector:delete-snapshot', DeleteSnapshotCommand::class);

        $this->app->bind('command.make:projector', MakeProjectorCommand::class);
        $this->app->bind('command.make:reactor', MakeReactorCommand::class);
        $this->app->bind('command.make:storable-event', MakeStorableEventCommand::class);

        $this->commands([
            'command.event-projector:list',
            'command.event-projector:reset',
            'command.event-projector:rebuild',
            'command.event-projector:replay',

            'command.event-projector:list-snapshots',
            'command.event-projector:create-snapshot',
            'command.event-projector:restore-snapshot',
            'command.event-projector:delete-snapshot',

            'command.make:projector',
            'command.make:reactor',
            'command.make:storable-event',
        ]);
    }
}
