<?php

namespace Spatie\EventProjector\Tests;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\HandleStoredEventJob;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventProjector\Tests\TestClasses\Mailables\AccountBroke;
use Spatie\EventProjector\Tests\TestClasses\Events\DoNotStoreThisEvent;
use Spatie\EventProjector\Tests\TestClasses\Projectors\QueuedProjector;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ProjectorThatInvokesAnObject;

final class EventSubscriberTest extends TestCase
{
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

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

        $this->assertCount(1, StoredEvent::get());

        $storedEvent = StoredEvent::first();

        $this->assertEquals(MoneyAddedEvent::class, $storedEvent->event_class);

        $this->assertInstanceOf(MoneyAddedEvent::class, $storedEvent->event);
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
}
