<?php

namespace Spatie\EventProjectors\Tests\Snapshots;

use Illuminate\Support\Facades\Storage;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Snapshots\SnapshotFactory;
use Spatie\EventProjector\Snapshots\SnapshotRepository;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Projectors\SnapshottableProjector;

class SnapshotFactoryTest extends TestCase
{
    /** @var \Spatie\EventProjector\Snapshots\SnapshotFactory */
    protected $snapshotFactory;

    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

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
    }

    /** @test */
    public function the_factory_can_create_a_snapshot_for_projector()
    {
        $snapshot = $this->snapshotFactory->createForProjector(new SnapshottableProjector());

        $this->assertEquals('', $snapshot->getName());

        $this->assertInstanceOf(SnapshottableProjector::class, $snapshot->getProjector());

        $this->assertEquals(0, $snapshot->getLastProcessedEventId());

        $serializedAccounts = json_encode(Account::get()->each->toArray());
        $this->assertEquals($serializedAccounts, $snapshot->read());
    }

    /** @test */
    public function it_will_correctly_get_the_last_processed_event_id_for_a_snapshot()
    {
        $amountOfEvents = 3;

        foreach (range(1, $amountOfEvents) as $i) {
            event(new MoneyAdded($this->account, 1000));
        }

        $snapshot = $this->snapshotFactory->createForProjector(new SnapshottableProjector());

        $this->assertEquals($amountOfEvents, $snapshot->getLastProcessedEventId());
    }
}