<?php

namespace Spatie\EventSourcerer;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventSourcererServiceProvider extends ServiceProvider
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

        /*
        $this->commands([
            'command.medialibrary:regenerate',
            'command.medialibrary:clear',
            'command.medialibrary:clean',
        ]);
        */

        $this->app->singleton(EventSourcerer::class, function () {
            return new EventSourcerer();
        });

        $this->app->alias(EventSourcerer::class, 'event-sorcerer');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/event-sorcerer.php', 'event-sorcerer');

        Event::subscribe(EventSubscriber::class);
    }
}
