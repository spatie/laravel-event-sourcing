<?php

namespace Spatie\EventSaucer\Tests;

use Illuminate\Support\Facades\Mail;
use Spatie\EventSaucer\StoredEvent;
use Spatie\EventSaucer\Tests\Events\DoNotStoreThisEvent;
use Spatie\EventSaucer\Tests\Events\MoneySubtracted;
use Spatie\EventSaucer\Tests\Mailables\AccountBroke;
use Spatie\EventSaucer\Tests\Models\Account;
use Spatie\EventSaucer\Tests\Events\MoneyAdded;
use Spatie\EventSaucer\Facades\EventSaucer;
use Spatie\EventSaucer\Tests\Mutators\BalanceMutator;
use Spatie\EventSaucer\Tests\Reactors\BrokeReactor;

class EventSubscriberTest extends TestCase
{
    /** @var \Spatie\EventSaucer\Tests\Models\Account */
    protected $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = Account::create();

        Mail::fake();
    }

    /** @test */
    public function it_will_log_events_that_implement_ShouldBeStored()
    {
        event(new MoneyAdded($this->account, 1234));

        $this->assertCount(1, StoredEvent::get());

        $event = StoredEvent::first()->event;

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

    /** @test */
    public function it_will_call_registered_mutators()
    {
        EventSaucer::addMutator(BalanceMutator::class);

        event(new MoneyAdded($this->account, 1234));
        $this->account->refresh();
        $this->assertEquals(1234, $this->account->amount);

        event(new MoneySubtracted($this->account, 34));
        $this->account->refresh();
        $this->assertEquals(1200, $this->account->amount);
    }

    /** @test */
    public function it_will_call_registered_reactors()
    {
        EventSaucer::addMutator(BalanceMutator::class);
        EventSaucer::addReactor(BrokeReactor::class);

        event(new MoneyAdded($this->account, 1234));
        Mail::assertNotSent(AccountBroke::class);

        event(new MoneySubtracted($this->account, 1000));
        Mail::assertNotSent(AccountBroke::class);

        event(new MoneySubtracted($this->account, 1000));
        Mail::assertSent(AccountBroke::class);
    }
}