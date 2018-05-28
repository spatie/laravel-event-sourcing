<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Snapshots\Snapshot;
use Spatie\EventProjector\Snapshots\SnapshotFactory;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector;

class ListSnapshotsCommandTest extends TestCase
{
    /** @test */
    public function it_can_list_all_existing_snapshots()
    {
        $snapshot = $this->takeSnapshot();

        $this->artisan('event-projector:list-snapshots');

        $this->assertSeeInConsoleOutput($snapshot->name());
    }

    /** @test */
    public function it_works_when_no_projectors_are_added_to_the_projectionist()
    {
        $this->artisan('event-projector:list-projectors');

        $this->assertSeeInConsoleOutput('No projectors found.');
    }

    protected function takeSnapshot(): Snapshot
    {
        EventProjectionist::addProjector(SnapshottableProjector::class);

        return app(SnapshotFactory::class)->createForProjector(new SnapshottableProjector());
    }
}
