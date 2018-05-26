<?php

namespace Spatie\EventProjector\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\EventProjector\EventProjectorServiceProvider;
use Spatie\SchemalessAttributes\SchemalessAttributesServiceProvider;

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
            EventProjectorServiceProvider::class,
            SchemalessAttributesServiceProvider::class,
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

    protected function setUpDatabase($app)
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('amount')->default(0);
            $table->timestamps();
        });

        include_once __DIR__.'/../stubs/create_stored_events_table.php.stub';
        (new \CreateStoredEventsTable())->up();
        include_once __DIR__.'/../stubs/create_projector_statuses_table.php.stub';
        (new \CreateProjectorStatusesTable())->up();
    }

    protected function assertSeeInConsoleOutput(string $text): self
    {
        $this->assertContains($text, Artisan::output());

        return $this;
    }
}
