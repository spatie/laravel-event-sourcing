<?php

namespace Spatie\EventSaucer;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventSaucerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/event-saucer.php' => config_path('event-saucer.php'),
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

        $this->app->singleton(EventSaucer::class, function () {
            return new EventSaucer();
        });

        $this->app->alias(EventSaucer::class, 'event-saucer');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/event-saucer.php', 'event-saucer');

        Event::subscribe(EventSubscriber::class);
    }
}
