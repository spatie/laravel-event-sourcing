<?php

namespace Spatie\EventProjector\Tests;

use Illuminate\Support\Facades\Mail;
use Spatie\EventProjector\StoredEvent;
use Spatie\EventProjector\Facades\EventProjector;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;
use Spatie\EventProjector\Tests\TestClasses\Mailables\AccountBroke;
use Spatie\EventProjector\Tests\TestClasses\Mutators\BalanceMutator;
use Spatie\EventProjector\Tests\TestClasses\Events\DoNotStoreThisEvent;

class EventSubscriberTest extends TestCase
{
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
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

        $storedEvent = StoredEvent::first();

        $this->assertEquals(MoneyAdded::class, $storedEvent->event_class);

        $this->assertInstanceOf(MoneyAdded::class, $storedEvent->event);
        $this->assertEquals(1234, $storedEvent->event->amount);
        $this->assertEquals($this->account->id, $storedEvent->event->account->id);
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
        EventProjector::addMutator(BalanceMutator::class);

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
        EventProjector::addMutator(BalanceMutator::class);
        EventProjector::addReactor(BrokeReactor::class);

        event(new MoneyAdded($this->account, 1234));
        Mail::assertNotSent(AccountBroke::class);

        event(new MoneySubtracted($this->account, 1000));
        Mail::assertNotSent(AccountBroke::class);

        event(new MoneySubtracted($this->account, 1000));
        Mail::assertSent(AccountBroke::class);
    }
}
