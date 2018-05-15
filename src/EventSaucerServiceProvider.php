<?php

namespace Spatie\Skeleton;

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
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/event-saucer.php', 'event-saucer');
    }
}
