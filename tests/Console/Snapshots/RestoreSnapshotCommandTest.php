<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Support\Facades\Storage;
use Spatie\EventProjector\Snapshots\SnapshotFactory;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Snapshots\SnapshotRepository;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector;

class RestoreSnapshotCommandTest extends TestCase
{
    public function setUp()
    {
        Storage::fake();
    }

    public function it_can_restore_a_snapshot()
    {
        $projector = new SnapshottableProjector();

        EventProjectionist::addProjector($projector);

        $account = Account::create();

        event(new MoneyAdded($account, 1234));

        app(SnapshotFactory::class)->createForProjector($projector);
    }
}