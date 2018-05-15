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

        if (! class_exists('CreateLoggedEvents')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_logged_events_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_logged_events_table.php'),
            ], 'migrations');
        }

        /*
        $this->commands([
            'command.medialibrary:regenerate',
            'command.medialibrary:clear',
            'command.medialibrary:clean',
        ]);
        */
    }

    public function register()
    {

        $this->mergeConfigFrom(__DIR__.'/../config/event-saucer.php', 'event-saucer');

        Event::subscribe(EventSubscriber::class);
    }
}
