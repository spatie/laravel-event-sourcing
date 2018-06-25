<?php

namespace Spatie\EventProjector\Tests;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\EventProjector\EventProjectorServiceProvider;

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
        ];
    }

    protected function setUpDatabase()
    {
        Schema::dropIfExists('accounts');

        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('amount')->default(0);
            $table->integer('addition_count')->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('stored_events');
        include_once __DIR__.'/../stubs/create_stored_events_table.php.stub';
        (new \CreateStoredEventsTable())->up();

        Schema::dropIfExists('projector_statuses');
        include_once __DIR__.'/../stubs/create_projector_statuses_table.php.stub';
        (new \CreateProjectorStatusesTable())->up();
    }

    protected function assertSeeInConsoleOutput(string $text): self
    {
        $this->assertContains($text, Artisan::output());

        return $this;
    }

    protected function setConfig(string $name, $value)
    {
        config()->set($name, $value);
        (new EventProjectorServiceProvider($this->app))->register();
    }
}
