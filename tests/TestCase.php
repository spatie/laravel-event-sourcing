<?php

namespace Spatie\EventSaucer\Tests;

use File;
use Carbon\Carbon;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\EventSaucer\EventSaucerServiceProvider;

abstract class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function getPackageProviders($app)
    {
        return [
            EventSaucerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('amount');
            $table->timestamps();
        });

        include_once __DIR__.'/../database/migrations/create_logged_events_table.php.stub';

        (new \CreateLoggedEventsTable())->up();
    }
}
