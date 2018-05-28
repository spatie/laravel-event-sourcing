<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

class ListSnapshotsCommandTest extends TestCase
{
    /** @test */
    public function it_can_list_all_existing_snapshots()
    {
        EventProjectionist::addProjector(BalanceProjector::class);

        $this->artisan('event-projector:list-projectors');

        $this
            ->assertSeeInConsoleOutput(BalanceProjector::class);
    }

    public function createSnapshot()
    {

    }
}
