<?php

namespace Spatie\EventProjector\Tests;

use Illuminate\Support\Facades\Mail;
use Spatie\EventProjector\Exceptions\InvalidEloquentStoredEventModel;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Models\EloquentStoredEvent;
use Spatie\EventProjector\Tests\TestClasses\FakeUuid;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Models\InvalidEloquentStoredEvent;
use Spatie\EventProjector\Tests\TestClasses\Models\OtherEloquentStoredEvent;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AccountAggregateRoot;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Reactors\SendMailReactor;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Mailable\MoneyAddedMailable;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Projectors\AccountProjector;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AccountAggregateRootWithStoredEventRepositorySpecified;

final class AggregateRootTest extends TestCase
{
    /** @var string */
    private $aggregateUuid;

    public function setUp(): void
    {
        parent::setUp();

        $this->aggregateUuid = FakeUuid::generate();
    }

    /** @test */
    public function persisting_an_aggregate_root_will_persist_all_events_it_recorded()
    {
        AccountAggregateRoot::retrieve($this->aggregateUuid)
            ->addMoney(100)
            ->persist();

        $storedEvents = EloquentStoredEvent::get();
        $this->assertCount(1, $storedEvents);

        $storedEvent = $storedEvents->first();
        $this->assertEquals($this->aggregateUuid, $storedEvent->aggregate_uuid);

        $event = $storedEvent->event;
        $this->assertInstanceOf(MoneyAdded::class, $event);
        $this->assertEquals(100, $event->amount);
    }

    /** @test */
    public function when_an_aggregate_root_specifies_a_stored_event_repository_persisting_will_persist_all_events_it_recorded_via_repository()
    {
        AccountAggregateRootWithStoredEventRepositorySpecified::retrieve($this->aggregateUuid)
            ->addMoney(100)
            ->persist();

        $storedEvents = EloquentStoredEvent::get();
        $this->assertCount(0, $storedEvents);

        $otherStoredEvents = OtherEloquentStoredEvent::get();
        $this->assertCount(1, $otherStoredEvents);

        $storedEvent = $otherStoredEvents->first();
        $this->assertEquals($this->aggregateUuid, $storedEvent->aggregate_uuid);

        $event = $storedEvent->event;
        $this->assertInstanceOf(MoneyAdded::class, $event);
        $this->assertEquals(100, $event->amount);
    }

    /** @test */
    public function when_an_the_config_specifies_a_stored_event_model_persisting_will_persist_all_events_it_recorded_via_stored_event()
    {
        config()->set('event-projector.stored_event_model', OtherEloquentStoredEvent::class);

        AccountAggregateRoot::retrieve($this->aggregateUuid)
            ->addMoney(100)
            ->persist();

        $storedEvents = EloquentStoredEvent::get();
        $this->assertCount(0, $storedEvents);

        $otherStoredEvents = OtherEloquentStoredEvent::get();
        $this->assertCount(1, $otherStoredEvents);

        $storedEvent = $otherStoredEvents->first();
        $this->assertEquals($this->aggregateUuid, $storedEvent->aggregate_uuid);

        $event = $storedEvent->event;
        $this->assertInstanceOf(MoneyAdded::class, $event);
        $this->assertEquals(100, $event->amount);
    }

    /** @test * */
    public function it_throws_an_error_when_defining_a_class_that_doesnt_extend_eloquent_stored_event()
    {
        config()->set('event-projector.stored_event_model', InvalidEloquentStoredEvent::class);

        $this->expectException(InvalidEloquentStoredEventModel::class);

        AccountAggregateRoot::retrieve($this->aggregateUuid)
            ->addMoney(100)
            ->persist();
    }

    /** @test */
    public function when_retrieving_an_aggregate_root_all_events_will_be_replayed_to_it()
    {
        /** @var \Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $aggregateRoot
            ->addMoney(100)
            ->addMoney(100)
            ->addMoney(100);

        $aggregateRoot->persist();

        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $this->assertEquals(300, $aggregateRoot->balance);
    }

    /** @test */
    public function when_retrieving_an_aggregate_root_all_events_will_be_replayed_to_it_in_the_correct_order()
    {
        /** @var \Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $aggregateRoot
            ->multiplyMoney(5)
            ->addMoney(100);

        $aggregateRoot->persist();

        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $this->assertEquals(100, $aggregateRoot->balance);
    }

    /** @test */
    public function when_retrieving_an_aggregate_root_all_events_will_be_replayed_to_it_with_the_stored_event_repository_specified()
    {
        /** @var \Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AccountAggregateRootWithStoredEventRepositorySpecified $aggregateRoot */
        $aggregateRoot = AccountAggregateRootWithStoredEventRepositorySpecified::retrieve($this->aggregateUuid);

        $aggregateRoot
            ->addMoney(100)
            ->addMoney(100)
            ->addMoney(100);

        $aggregateRoot->persist();

        $this->assertEquals(0, EloquentStoredEvent::count());
        $this->assertEquals(3, OtherEloquentStoredEvent::count());

        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);
        $this->assertEquals(0, $aggregateRoot->balance);

        $aggregateRoot = AccountAggregateRootWithStoredEventRepositorySpecified::retrieve($this->aggregateUuid);
        $this->assertEquals(300, $aggregateRoot->balance);
    }

    /** @test */
    public function a_recorded_event_immediately_gets_applied()
    {
        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);
        $aggregateRoot->addMoney(123);

        $this->assertEquals(123, $aggregateRoot->balance);
    }

    /** @test */
    public function projectors_will_get_called_when_an_aggregate_root_is_persisted()
    {
        Projectionist::addProjector(AccountProjector::class);

        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $aggregateRoot->addMoney(123);

        $accounts = Account::get();
        $this->assertCount(0, $accounts);

        $aggregateRoot->persist();

        $accounts = Account::get();
        $this->assertCount(1, $accounts);

        $account = Account::first();
        $this->assertEquals(123, $account->amount);
        $this->assertEquals($this->aggregateUuid, $account->uuid);
    }

    /** @test */
    public function reactors_will_get_called_when_an_aggregate_root_is_persisted()
    {
        Projectionist::addReactor(SendMailReactor::class);

        Mail::fake();

        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $aggregateRoot->addMoney(123);

        Mail::assertNothingSent();

        $aggregateRoot->persist();

        Mail::assertSent(MoneyAddedMailable::class, function (MoneyAddedMailable $mailable) {
            $this->assertEquals($this->aggregateUuid, $mailable->aggregateUuid);
            $this->assertEquals(123, $mailable->amount);

            return true;
        });
    }
}
