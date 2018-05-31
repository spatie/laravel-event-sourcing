<?php

namespace Spatie\EventProjectors\Tests\Snapshots;

use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Snapshots\SnapshotFactory;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector;

class SnapshotTest extends TestCase
{
    /** @test */
    public function it_can_restore_a_snapshot()
    {
        $projector = new SnapshottableProjector();

        EventProjectionist::addProjector($projector);

        $account = Account::create();

        event(new MoneyAdded($account, 1234));

        $snapshot = app(SnapshotFactory::class)->createForProjector($projector);

        Account::truncate();
        ProjectorStatus::truncate();

        $snapshot->restore();

        $this->assertTrue(true);
    }
}