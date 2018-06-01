<?php

namespace Spatie\EventProjectors\Tests\Snapshots;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Snapshots\SnapshotFactory;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector;

class SnapshotTest extends TestCase
{
    /** @var \Spatie\EventProjector\Snapshots\SnapshotFactory */
    protected $snapshotFactory;

    /** @var \Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector */
    protected $projector;

    public function setUp()
    {
        parent::setUp();

        $this->snapshotFactory = app(SnapshotFactory::class);

        $this->projector = new SnapshottableProjector();

        EventProjectionist::addProjector($this->projector);

        $account = Account::create();

        event(new MoneyAdded($account, 1000));
        event(new MoneyAdded($account, 1000));
        event(new MoneyAdded($account, 1000));
    }

    /** @test */
    public function it_can_restore_a_snapshot()
    {
        $snapshot = $this->snapshotFactory->createForProjector($this->projector);

        Account::truncate();
        ProjectorStatus::truncate();

        $snapshot->restore();

        $this->assertCount(1, Account::get());
        $this->assertEquals(3, ProjectorStatus::getForProjector($this->projector)->last_processed_event_id);
    }
}
