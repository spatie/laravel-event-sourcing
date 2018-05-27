<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Support\Facades\Storage;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector;

class CreateSnapshotCommandTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Storage::fake();
    }

    /** @test */
    public function it_can_create_a_snapshot()
    {
        EventProjectionist::addProjector(SnapshottableProjector::class);

        $this->artisan('event-projector:create-snapshot', [
            'projectorName' => SnapshottableProjector::class
        ]);
    }
}
