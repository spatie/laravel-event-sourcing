<?php

namespace Spatie\EventSourcing\Tests;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\HandleStoredEventJob;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\DoNotStoreThisEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEventWithQueueOverride;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Mailables\AccountBroke;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorThatInvokesAnObject;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\QueuedProjector;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\SyncBrokeReactor;

class EventSubscriberTest extends TestCase
{
    protected Account $account;

    public function setUp(): void
    {
        parent::setUp();

        $this->account = Account::create();

        Mail::fake();
    }

    /** @test */
    public function it_will_log_events_that_implement_ShouldBeStored()
    {
        event(new MoneyAddedEvent($this->account, 1234));

        $this->assertCount(1, EloquentStoredEvent::get());

        $storedEvent = EloquentStoredEvent::first();

        $this->assertEquals(MoneyAddedEvent::class, $storedEvent->event_class);

        $this->assertInstanceOf(MoneyAddedEvent::class, $storedEvent->event);
        $this->assertEquals(1234, $storedEvent->event->amount);
        $this->assertEquals($this->account->id, $storedEvent->event->account->id);
    }

    /** @test * */
    public function it_will_log_events_that_implement_ShouldBeStored_with_a_map()
    {
        $this->setConfig('event-sourcing.event_class_map', [
            'moneyadd' => MoneyAddedEvent::class,
        ]);

        event(new MoneyAddedEvent($this->account, 1234));

        $this->assertCount(1, EloquentStoredEvent::get());

        $storedEvent = EloquentStoredEvent::first();

        $this->assertDatabaseHas('stored_events', ['event_class' => 'moneyadd']);

        $this->assertInstanceOf(MoneyAddedEvent::class, $storedEvent->event);
        $this->assertEquals(1234, $storedEvent->event->amount);
        $this->assertEquals($this->account->id, $storedEvent->event->account->id);
    }

    /** @test */
    public function it_will_not_store_events_without_the_ShouldBeStored_interface()
    {
        event(new DoNotStoreThisEvent());

        $this->assertCount(0, EloquentStoredEvent::get());
    }

    /** @test */
    public function it_will_not_store_events_when_events_are_fired_from_a_aggregate_root()
    {
        $event = new MoneyAddedEvent($this->account, 1234);
        $event->firedFromAggregateRoot = true;

        event($event);

        $this->assertCount(0, EloquentStoredEvent::get());
    }

    /** @test */
    public function it_will_call_registered_projectors()
    {
        Projectionist::addProjector(BalanceProjector::class);

        event(new MoneyAddedEvent($this->account, 1234));
        $this->account->refresh();
        $this->assertEquals(1234, $this->account->amount);

        event(new MoneySubtractedEvent($this->account, 34));
        $this->account->refresh();
        $this->assertEquals(1200, $this->account->amount);
    }

    /** @test */
    public function it_will_call_registered_projectors_that_invokes_an_object()
    {
        Projectionist::addProjector(ProjectorThatInvokesAnObject::class);

        event(new MoneyAddedEvent($this->account, 1234));
        $this->account->refresh();
        $this->assertEquals(1234, $this->account->amount);
    }

    /** @test */
    public function it_will_call_registered_reactors()
    {
        Projectionist::addProjector(BalanceProjector::class);
        Projectionist::addReactor(BrokeReactor::class);

        event(new MoneyAddedEvent($this->account, 1234));
        Mail::assertNotSent(AccountBroke::class);

        event(new MoneySubtractedEvent($this->account, 1000));
        Mail::assertNotSent(AccountBroke::class);

        event(new MoneySubtractedEvent($this->account, 1000));
        Mail::assertSent(AccountBroke::class);
    }

    /** @test */
    public function it_will_not_queue_event_handling_by_default()
    {
        Bus::fake();

        $projector = new BalanceProjector();
        Projectionist::addProjector($projector);

        event(new MoneyAddedEvent($this->account, 1000));

        $this->assertEquals(1000, $this->account->refresh()->amount);
    }

    /** @test */
    public function a_queued_projector_will_be_queued()
    {
        Bus::fake();

        $projector = new QueuedProjector();
        Projectionist::addProjector($projector);

        event(new MoneyAddedEvent($this->account, 1234));

        Bus::assertDispatched(HandleStoredEventJob::class, function (HandleStoredEventJob $job) {
            return get_class($job->storedEvent->event) === MoneyAddedEvent::class;
        });

        $this->assertEquals(0, $this->account->refresh()->amount);
    }

    /** @test */
    public function a_queued_reactor_will_be_queued()
    {
        Bus::fake();

        Projectionist::addProjector(BalanceProjector::class);
        Projectionist::addReactor(BrokeReactor::class);

        event(new MoneySubtractedEvent($this->account, 1000));

        Bus::assertDispatched(HandleStoredEventJob::class, function (HandleStoredEventJob $job) {
            return get_class($job->storedEvent->event) === MoneySubtractedEvent::class;
        });
    }

    /** @test */
    public function a_non_queued_reactor_will_not_be_queued()
    {
        Bus::fake();

        Projectionist::addProjector(BalanceProjector::class);
        Projectionist::addReactor(SyncBrokeReactor::class);

        event(new MoneySubtractedEvent($this->account, 1000));

        Bus::assertNotDispatched(HandleStoredEventJob::class);
    }

    /** @test */
    public function it_calls_sync_projectors_but_does_not_dipatch_job_if_event_has_no_queued_projectors_and_no_reactors()
    {
        Bus::fake();

        $projector = new BalanceProjector();
        Projectionist::addProjector($projector);

        event(new MoneyAddedEvent($this->account, 1234));

        Bus::assertNotDispatched(HandleStoredEventJob::class);

        $this->assertEquals(1234, $this->account->refresh()->amount);
    }

    /** @test */
    public function event_without_queue_override_will_be_queued_correctly()
    {
        Queue::fake();

        $this->setConfig('event-sourcing.queue', 'defaultQueue');

        $projector = new QueuedProjector();
        Projectionist::addProjector($projector);

        event(new MoneyAddedEvent($this->account, 1234));

        Queue::assertPushedOn('defaultQueue', HandleStoredEventJob::class);
    }

    /** @test */
    public function event_with_queue_override_will_be_queued_correctly()
    {
        Queue::fake();

        $this->setConfig('event-sourcing.queue', 'defaultQueue');

        $projector = new QueuedProjector();
        Projectionist::addProjector($projector);

        event(new MoneyAddedEventWithQueueOverride($this->account, 1234));

        Queue::assertPushedOn('testQueue', HandleStoredEventJob::class);
    }
}
