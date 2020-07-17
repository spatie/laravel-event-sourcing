<?php

namespace Spatie\EventSourcing\Tests;

use CreateStoredEventsTable;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\EventSourcing\EventSourcingServiceProvider;
use Spatie\EventSourcing\Tests\TestClasses\FakeUuid;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();

        FakeUuid::reset();

        $this->artisan('event-sourcing:clear-event-handlers');
    }

    protected function getPackageProviders($app)
    {
        return [
            EventSourcingServiceProvider::class,
        ];
    }

    protected function setUpDatabase()
    {
        Schema::dropIfExists('accounts');

        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('uuid')->nullable();
            $table->integer('amount')->default(0);
            $table->integer('addition_count')->default(0);
            $table->timestamps();
        });

        Schema::dropIfExists('stored_events');
        include_once __DIR__.'/../stubs/create_stored_events_table.php.stub';
        (new CreateStoredEventsTable())->up();

        Schema::dropIfExists('snapshots');
        include_once __DIR__.'/../stubs/create_snapshots_table.php.stub';
        (new \CreateSnapshotsTable())->up();

        Schema::dropIfExists('other_stored_events');
        if ($this->dbDriver() === 'mysql') {
            DB::statement('CREATE TABLE other_stored_events LIKE stored_events');
        } elseif ($this->dbDriver() === 'pgsql') {
            DB::statement('CREATE TABLE other_stored_events AS TABLE stored_events;');
        } else {
            throw new Exception(
                sprintf('DB driver [%s] is not supported by this test suite.', $this->dbDriver())
            );
        }
    }

    protected function assertSeeInConsoleOutput(string $text): self
    {
        $this->assertStringContainsString($text, Artisan::output());

        return $this;
    }

    protected function setConfig(string $name, $value)
    {
        config()->set($name, $value);

        (new EventSourcingServiceProvider($this->app))->register();
    }

    protected function pathToTests(): string
    {
        return __DIR__;
    }

    protected function dbDriver(): string
    {
        $connection = config('database.default');

        return config("database.connections.{$connection}.driver");
    }

    protected function assertTestPassed(): void
    {
        $this->assertTrue(true);
    }

    protected function assertExceptionThrown(
        callable $callable,
        string $expectedExceptionClass = Exception::class
    ): void {
        try {
            $callable();

            $this->assertTrue(false, "Expected exception `{$expectedExceptionClass}` was not thrown.");
        } catch (Exception $exception) {
            $actualExceptionClass = get_class($exception);

            $this->assertInstanceOf($expectedExceptionClass, $exception, "Unexpected exception `$actualExceptionClass` thrown. Expected exception `$expectedExceptionClass`");
        }
    }
}
