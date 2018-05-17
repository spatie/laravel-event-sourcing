<?php

namespace Spatie\EventSorcerer;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\EventSorcerer\Console\ReplayEventsCommand;

class EventSorcererServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/event-sorcerer.php' => config_path('event-sorcerer.php'),
            ], 'config');
        }

        if (! class_exists('CreateStoredEventsTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_stored_events_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_stored_events_table.php'),
            ], 'migrations');
        }

        $this->app->bind('command.event-sorcerer:replay-events', ReplayEventsCommand::class);

        $this->commands([
            'command.event-sorcerer:replay-events',
        ]);

        $this->app->singleton(EventSorcerer::class, function () {
            return new EventSorcerer();
        });

        $this->app->alias(EventSorcerer::class, 'event-sorcerer');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/event-sorcerer.php', 'event-sorcerer');

        Event::subscribe(EventSubscriber::class);
    }
}
