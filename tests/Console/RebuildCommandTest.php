<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ResettableProjector;

class RebuildCommandTest extends TestCase
{
    /** @test */
    public function it_can_rebuild_a_projector()
    {
        EventProjectionist::addProjector(ResettableProjector::class);

        ProjectorStatus::getForProjector(new ResettableProjector());
        $this->assertCount(1, ProjectorStatus::get());

        $this->artisan('event-projector:rebuild', [
            'projector' => [ResettableProjector::class],
        ]);

        $this->assertSeeInConsoleOutput('Projector(s) rebuild!');

        $this->assertCount(1, ProjectorStatus::get());
    }
}
