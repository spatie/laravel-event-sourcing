<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Support\Facades\Storage;
use Mockery;
use Spatie\EventProjector\Snapshots\SnapshotFactory;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Snapshots\SnapshotRepository;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector;

class RestoreSnapshotCommandTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Storage::fake();
    }

    /** @test */
    public function it_can_restore_a_snapshot()
    {
        $projector = new SnapshottableProjector();
        EventProjectionist::addProjector($projector);

        $account = Account::create();
        event(new MoneyAdded($account, 1234));

        app(SnapshotFactory::class)->createForProjector($projector);
        Account::truncate();

        $this->chooseSnapshot(1);

        $this->assertSeeInConsoleOutput('Snapshot restored!');

        $this->assertCount(1, Account::get());
    }

    protected function chooseSnapshot(int $chosenSnapshotNumber)
    {
        $command = Mockery::mock(RestoreSnapshotCommand::class . '[ask]', [
            app(SnapshotRepository::class),
        ]);

        $command->shouldReceive('ask')->andReturn($chosenSnapshotNumber);

        $this->app->bind('command.event-projector:restore-snapshot', function () use ($command) {
            return $command;
        });

        $this->artisan('event-projector:restore-snapshot');
    }
}