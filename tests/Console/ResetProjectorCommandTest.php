<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Support\Facades\Storage;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Snapshots\SnapshotRepository;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ResettableProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector;

class ResetProjectorCommandTest extends TestCase
{
    /** @test */
    public function it_can_reset_a_projector()
    {
        EventProjectionist::addProjector(ResettableProjector::class);

        ProjectorStatus::getForProjector(new ResettableProjector());
        $this->assertCount(1, ProjectorStatus::get());

        $this->artisan('event-projector:reset-projector', [
            'projectorName' => ResettableProjector::class,
        ]);

        $this->assertSeeInConsoleOutput('Projector reset');

        $this->assertCount(0, ProjectorStatus::get());
    }
}
