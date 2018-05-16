<?php

namespace Spatie\EventSaucer\Tests;

use Spatie\EventSaucer\StoredEvent;
use Spatie\EventSaucer\Tests\Events\DoNotStoreThisEvent;
use Spatie\EventSaucer\Tests\Models\Account;
use Spatie\EventSaucer\Tests\Events\MoneyAdded;

class EventSubscriberTest extends TestCase
{
    /** @var \Spatie\EventSaucer\Tests\Models\Account */
    protected $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = Account::create();
    }

    /** @test */
    public function it_will_log_events_that_are_marked_with_should_be_stored()
    {
        event(new MoneyAdded($this->account, 1234));

        $this->assertCount(1, StoredEvent::get());

        $event = StoredEvent::first()->event;

        dump(StoredEvent::first()->event_properties);

        $this->assertInstanceOf(MoneyAdded::class, $event);

        $this->assertEquals(1234, $event->amount);

        $this->assertEquals($this->account->id, $event->account->id);
    }

    /** @test */
    public function it_will_not_store_events_without_the_ShouldBeStored_interface()
    {
        event(new DoNotStoreThisEvent());

        $this->assertCount(0, StoredEvent::get());
    }
}