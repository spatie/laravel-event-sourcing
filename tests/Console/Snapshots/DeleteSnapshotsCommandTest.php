<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Support\Facades\Storage;
use Mockery;
use Spatie\EventProjector\Snapshots\SnapshotRepository;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Snapshots\Snapshot;
use Spatie\EventProjector\Snapshots\SnapshotFactory;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector;

class DeleteSnapshotsCommandTest extends TestCase
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

        $command = Mockery::mock(DeleteSnapshotCommand::class . '[ask]', [
            app(SnapshotRepository::class),
        ]);

        $command->shouldReceive('ask')->andReturn(1);

        $this->app->bind('command.event-projector:delete-snapshot', function () use ($command) {
            return $command;
        });

        $this->artisan('event-projector:delete-snapshot');

        $leftoverSnapshotNames = app(SnapshotRepository::class)
            ->get()
            ->map(function(Snapshot $snapshot) {
                return $snapshot->name();
            })
            ->toArray();

        $this->assertEquals(['snapshot-2', 'snapshot-3'], $leftoverSnapshotNames);
    }

    protected function takeSnapshot(string $customName): Snapshot
    {
        EventProjectionist::addProjector(SnapshottableProjector::class);

        return app(SnapshotFactory::class)->createForProjector(new SnapshottableProjector(), $customName);
    }
}
