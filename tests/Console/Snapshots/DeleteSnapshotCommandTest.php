<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Mockery;
use Illuminate\Support\Facades\Storage;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Snapshots\Snapshot;
use Spatie\EventProjector\Snapshots\SnapshotFactory;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Snapshots\SnapshotRepository;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector;

class DeleteSnapshotCommandTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Storage::fake();
    }

    /** @test */
    public function it_can_delete_a_snapshot()
    {
        $this->takeSnapshot('snapshot-1');
        $this->takeSnapshot('snapshot-2');
        $this->takeSnapshot('snapshot-3');

        $this->chooseSnapshot(1);

        $leftoverSnapshotNames = app(SnapshotRepository::class)
            ->get()
            ->sortBy(function (Snapshot $snapshot) {
                return $snapshot->name();
            })
            ->map(function (Snapshot $snapshot) {
                return $snapshot->name();
            })
            ->toArray();

        $this->assertEquals(['snapshot-2', 'snapshot-3'], $leftoverSnapshotNames);
    }

    /** @test */
    public function it_will_not_delete_a_snapshot_if_an_invalid_number_is_answered()
    {
        $this->takeSnapshot('snapshot-1');
        $this->takeSnapshot('snapshot-2');
        $this->takeSnapshot('snapshot-3');

        $this->chooseSnapshot(1000);

        $this->assertCount(3, app(SnapshotRepository::class)->get());
    }

    protected function takeSnapshot(string $customName): Snapshot
    {
        EventProjectionist::addProjector(SnapshottableProjector::class);

        return app(SnapshotFactory::class)->createForProjector(new SnapshottableProjector(), $customName);
    }

    protected function chooseSnapshot(int $chosenSnapshotNumber)
    {
        $command = Mockery::mock(DeleteSnapshotCommand::class.'[ask]', [
            app(SnapshotRepository::class),
        ]);

        $command->shouldReceive('ask')->andReturn($chosenSnapshotNumber);

        $this->app->bind('command.event-projector:delete-snapshot', function () use ($command) {
            return $command;
        });

        $this->artisan('event-projector:delete-snapshot');
    }
}
