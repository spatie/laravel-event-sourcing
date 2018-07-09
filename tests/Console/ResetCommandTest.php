<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ResettableProjector;

class ResetCommandTest extends TestCase
{
    /** @test */
    public function it_can_reset_a_projector()
    {
        Projectionist::addProjector(ResettableProjector::class);

        ProjectorStatus::getForProjector(new ResettableProjector());
        $this->assertCount(1, ProjectorStatus::get());

        $this->artisan('event-projector:reset', [
            'projector' => [ResettableProjector::class],
        ]);

        $this->assertSeeInConsoleOutput('Projector(s) reset');

        $this->assertCount(0, ProjectorStatus::get());
    }
}
