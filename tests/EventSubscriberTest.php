<?php

namespace Spatie\EventSourcing\Tests;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

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
use Spatie\EventSourcing\Tests\TestClasses\Projectors\QueuedProjector;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\SyncBrokeReactor;

beforeEach(function () {
    $this->account = Account::create();

    Mail::fake();
});

it('will log events that implement ShouldBeStored', function () {
    event(new MoneyAddedEvent($this->account, 1234));

    assertCount(1, EloquentStoredEvent::get());

    $storedEvent = EloquentStoredEvent::first();

    assertEquals(MoneyAddedEvent::class, $storedEvent->event_class);

    assertInstanceOf(MoneyAddedEvent::class, $storedEvent->event);
    assertEquals(1234, $storedEvent->event->amount);
    assertEquals($this->account->id, $storedEvent->event->account->id);
});

it('will log events that implement ShouldBeStored with a map', function () {
    $this->setConfig('event-sourcing.event_class_map', [
        'moneyadd' => MoneyAddedEvent::class,
    ]);

    event(new MoneyAddedEvent($this->account, 1234));

    assertCount(1, EloquentStoredEvent::get());

    $storedEvent = EloquentStoredEvent::first();

    $this->assertDatabaseHas('stored_events', ['event_class' => 'moneyadd']);

    assertInstanceOf(MoneyAddedEvent::class, $storedEvent->event);
    assertEquals(1234, $storedEvent->event->amount);
    assertEquals($this->account->id, $storedEvent->event->account->id);
});

it('will not store events without the ShouldBeStored interface', function () {
    event(new DoNotStoreThisEvent());

    assertCount(0, EloquentStoredEvent::get());
});

it('will not store events when events are fired from a aggregate root', function () {
    $event = new MoneyAddedEvent($this->account, 1234);
    $event->firedFromAggregateRoot = true;

    event($event);

    assertCount(0, EloquentStoredEvent::get());
});

it('will call registered projectors', function () {
    Projectionist::addProjector(BalanceProjector::class);

    event(new MoneyAddedEvent($this->account, 1234));
    $this->account->refresh();
    assertEquals(1234, $this->account->amount);

    event(new MoneySubtractedEvent($this->account, 34));
    $this->account->refresh();
    assertEquals(1200, $this->account->amount);
});

it('will call registered reactors', function () {
    Projectionist::addProjector(BalanceProjector::class);
    Projectionist::addReactor(BrokeReactor::class);

    event(new MoneyAddedEvent($this->account, 1234));
    Mail::assertNotSent(AccountBroke::class);

    event(new MoneySubtractedEvent($this->account, 1000));
    Mail::assertNotSent(AccountBroke::class);

    event(new MoneySubtractedEvent($this->account, 1000));
    Mail::assertSent(AccountBroke::class);
});

it('will not queue event handling by default', function () {
    Bus::fake();

    $projector = new BalanceProjector();
    Projectionist::addProjector($projector);

    event(new MoneyAddedEvent($this->account, 1000));

    assertEquals(1000, $this->account->refresh()->amount);
});

it('should queue a queued projector', function () {
    Bus::fake();

    $projector = new QueuedProjector();
    Projectionist::addProjector($projector);

    event(new MoneyAddedEvent($this->account, 1234));

    Bus::assertDispatched(HandleStoredEventJob::class, function (HandleStoredEventJob $job) {
        return get_class($job->storedEvent->event) === MoneyAddedEvent::class;
    });

    assertEquals(0, $this->account->refresh()->amount);
});

it('should queue a queued reactor', function () {
    Bus::fake();

    Projectionist::addProjector(BalanceProjector::class);
    Projectionist::addReactor(BrokeReactor::class);

    event(new MoneySubtractedEvent($this->account, 1000));

    Bus::assertDispatched(HandleStoredEventJob::class, function (HandleStoredEventJob $job) {
        return get_class($job->storedEvent->event) === MoneySubtractedEvent::class;
    });
});

it('should not queue a non queued reactor', function () {
    Bus::fake();

    Projectionist::addProjector(BalanceProjector::class);
    Projectionist::addReactor(SyncBrokeReactor::class);

    event(new MoneySubtractedEvent($this->account, 1000));

    Bus::assertNotDispatched(HandleStoredEventJob::class);
});

it('calls sync projectors but does not dipatch job if event has no queued projectors and no reactors', function () {
    Bus::fake();

    $projector = new BalanceProjector();
    Projectionist::addProjector($projector);

    event(new MoneyAddedEvent($this->account, 1234));

    Bus::assertNotDispatched(HandleStoredEventJob::class);

    assertEquals(1234, $this->account->refresh()->amount);
});

it('should queue event without queue override', function () {
    Queue::fake();

    $this->setConfig('event-sourcing.queue', 'defaultQueue');

    $projector = new QueuedProjector();
    Projectionist::addProjector($projector);

    event(new MoneyAddedEvent($this->account, 1234));

    Queue::assertPushedOn('defaultQueue', HandleStoredEventJob::class);
});

it('should queue event with queue override', function () {
    Queue::fake();

    $this->setConfig('event-sourcing.queue', 'defaultQueue');

    $projector = new QueuedProjector();
    Projectionist::addProjector($projector);

    event(new MoneyAddedEventWithQueueOverride($this->account, 1234));

    Queue::assertPushedOn('testQueue', HandleStoredEventJob::class);
});
