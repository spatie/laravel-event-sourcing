<?php

namespace Spatie\EventProjectors\Tests\Snapshots;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\EventProjector\Exceptions\CouldNotCreateSnapshot;
use Spatie\EventProjector\Exceptions\InvalidSnapshot;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Snapshots\SnapshotFactory;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableThatDoesNotWriteAnythingProjector;

class SnapshotFactoryTest extends TestCase
{
    /** @var \Spatie\EventProjector\Snapshots\SnapshotFactory */
    protected $snapshotFactory;

    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

    /** @var \Spatie\EventProjector\Snapshots\Snapshot */
    protected $projectorSnapshot;

    public function setUp()
    {
        parent::setUp();

        Storage::fake();

        $this->snapshotFactory = app(SnapshotFactory::class);

        $this->account = Account::create([
            'name' => 'John',
            'amount' => 1000,
        ]);

        EventProjectionist::addProjector(SnapshottableProjector::class);

        $this->projectorSnapshot = $this->snapshotFactory->createForProjector(new SnapshottableProjector());
    }

    /** @test */
    public function the_factory_can_create_a_snapshot_for_projector()
    {
        $this->assertEquals('', $this->projectorSnapshot->name());

        $this->assertInstanceOf(SnapshottableProjector::class, $this->projectorSnapshot->projector());

        $this->assertEquals(0, $this->projectorSnapshot->lastProcessedEventId());

        $this->assertEquals(date('Ymd'), $this->projectorSnapshot->createdAt()->format('Ymd'));

        $serializedAccounts = json_encode(Account::get()->each->toArray());
        $this->assertEquals($serializedAccounts, $this->projectorSnapshot->read());
    }

    /** @test */
    public function it_will_correctly_get_the_last_processed_event_id_for_a_snapshot()
    {
        $amountOfEvents = 3;

        foreach (range(1, $amountOfEvents) as $i) {
            event(new MoneyAdded($this->account, 1000));
        }

        $snapshot = $this->snapshotFactory->createForProjector(new SnapshottableProjector());

        $this->assertEquals($amountOfEvents, $snapshot->lastProcessedEventId());
    }

    /** @test */
    public function it_can_return_the_date_is_was_created()
    {
        $this->assertInstanceOf(Carbon::class, $this->projectorSnapshot->createdAt());
    }

    /** @test */
    public function it_will_throw_an_exception_when_a_snapshot_does_not_get_written_to()
    {
        $this->expectException(CouldNotCreateSnapshot::class);

        $this->snapshotFactory->createForProjector(new SnapshottableThatDoesNotWriteAnythingProjector());
    }
}
