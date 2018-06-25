<?php

namespace Spatie\EventProjector\Tests;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\HandleStoredEventJob;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;
use Spatie\EventProjector\Tests\TestClasses\Mailables\AccountBroke;
use Spatie\EventProjector\Tests\TestClasses\Events\DoNotStoreThisEvent;
use Spatie\EventProjector\Tests\TestClasses\Projectors\QueuedProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

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
    public function it_will_call_registered_projectors()
    {
        EventProjectionist::addProjector(BalanceProjector::class);

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
        EventProjectionist::addProjector(BalanceProjector::class);
        EventProjectionist::addReactor(BrokeReactor::class);

        event(new MoneyAdded($this->account, 1234));
        Mail::assertNotSent(AccountBroke::class);

        event(new MoneySubtracted($this->account, 1000));
        Mail::assertNotSent(AccountBroke::class);

        event(new MoneySubtracted($this->account, 1000));
        Mail::assertSent(AccountBroke::class);
    }

    /** @test */
    public function it_will_not_queue_event_handling_by_default()
    {
        Bus::fake();

        $projector = new BalanceProjector();
        EventProjectionist::addProjector($projector);

        event(new MoneyAdded($this->account, 1000));

        $status = ProjectorStatus::getForProjector($projector);

        $this->assertEquals(1000, $this->account->refresh()->amount);
        $this->assertEquals(1, $status->last_processed_event_id);
    }

    /** @test */
    public function a_queued_projector_will_be_queued()
    {
        Bus::fake();

        $projector = new QueuedProjector();
        EventProjectionist::addProjector($projector);

        event(new MoneyAdded($this->account, 1234));

        Bus::assertDispatched(HandleStoredEventJob::class, function (HandleStoredEventJob $job) {
            return get_class($job->storedEvent->event) === MoneyAdded::class;
        });

        $status = ProjectorStatus::getForProjector($projector);
        $this->assertEquals(0, $status->last_processed_event_id);
        $this->assertEquals(0, $this->account->refresh()->amount);
    }
}
