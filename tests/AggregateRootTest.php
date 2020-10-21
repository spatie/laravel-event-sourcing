<?php

namespace Spatie\EventSourcing\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Spatie\EventSourcing\Exceptions\CouldNotPersistAggregate;
use Spatie\EventSourcing\Exceptions\InvalidEloquentStoredEventModel;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\Snapshots\EloquentSnapshot;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRootThatAllowsConcurrency;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRootWithFailingPersist;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRootWithStoredEventRepositorySpecified;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Mailable\MoneyAddedMailable;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors\AccountProjector;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Reactors\SendMailReactor;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAdded;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyMultiplied;
use Spatie\EventSourcing\Tests\TestClasses\FakeUuid;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Models\InvalidEloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\OtherEloquentStoredEvent;

class AggregateRootTest extends TestCase
{
    private string $aggregateUuid;

    public function setUp(): void
    {
        parent::setUp();

        $this->aggregateUuid = FakeUuid::generate();
    }

    /** @test */
    public function aggregate_root_resolves_dependencies_from_the_container()
    {
        $this->app->bind(AccountAggregateRoot::class, function () {
            return new AccountAggregateRoot(42);
        });

        $root = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $this->assertEquals(42, $root->dependency);
    }

    /** @test */
    public function it_can_get_the_uuid()
    {
        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $this->assertEquals($this->aggregateUuid, $aggregateRoot->uuid());
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
        config()->set('event-sourcing.stored_event_model', OtherEloquentStoredEvent::class);

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
        config()->set('event-sourcing.stored_event_model', InvalidEloquentStoredEvent::class);

        $this->expectException(InvalidEloquentStoredEventModel::class);

        AccountAggregateRoot::retrieve($this->aggregateUuid)
            ->addMoney(100)
            ->persist();
    }

    /** @test */
    public function when_retrieving_an_aggregate_root_all_events_will_be_replayed_to_it()
    {
        /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
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
    public function when_applying_events_it_increases_the_version_number()
    {
        /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $aggregateRoot
            ->addMoney(100)
            ->addMoney(100)
            ->addMoney(100);

        $this->assertEquals(3, $aggregateRoot->aggregateVersion);
    }

    /** @test */
    public function snapshotting_stores_public_properties_and_version_number()
    {
        /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $aggregateRoot
            ->addMoney(100)
            ->addMoney(100)
            ->addMoney(100);

        $this->assertEquals(0, EloquentSnapshot::count());

        $aggregateRoot->snapshot();

        $this->assertEquals(1, EloquentSnapshot::count());
        tap(EloquentSnapshot::first(), function (EloquentSnapshot $snapshot) {
            $this->assertEquals(300, $snapshot->state['balance']);
            $this->assertEquals(3, $snapshot->aggregate_version);
        });
    }

    /** @test */
    public function restoring_an_aggregate_root_with_a_snapshot_restores_public_properties()
    {
        /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $aggregateRoot
            ->addMoney(100)
            ->addMoney(100)
            ->addMoney(100);

        $aggregateRoot->snapshot();

        $aggregateRootRetrieved = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $this->assertEquals(3, $aggregateRootRetrieved->aggregateVersion);
        $this->assertEquals(300, $aggregateRootRetrieved->balance);
    }

    /** @test */
    public function events_saved_after_the_snapshot_are_reconstituted()
    {
        /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $aggregateRoot
            ->addMoney(100)
            ->addMoney(100)
            ->addMoney(100)
            ->persist();

        $aggregateRoot->snapshot();
        $aggregateRoot->addMoney(100)->persist();

        $aggregateRootRetrieved = AccountAggregateRoot::retrieve($this->aggregateUuid);

        $this->assertEquals(4, $aggregateRootRetrieved->aggregateVersion);
        $this->assertEquals(400, $aggregateRootRetrieved->balance);
    }

    /** @test */
    public function when_retrieving_an_aggregate_root_all_events_will_be_replayed_to_it_in_the_correct_order()
    {
        /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
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
        /** @var \Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRootWithStoredEventRepositorySpecified $aggregateRoot */
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
    public function it_can_persist_aggregate_roots_in_a_transaction()
    {
        Mail::fake();

        Projectionist::addProjector(AccountProjector::class);
        Projectionist::addReactor(SendMailReactor::class);

        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid)->addMoney(123);
        AggregateRoot::persistInTransaction($aggregateRoot);

        $this->assertCount(1, EloquentStoredEvent::get());
        $this->assertCount(1, Account::get());
        Mail::assertSent(MoneyAddedMailable::class);
    }

    /** @test */
    public function it_will_not_call_any_event_handlers_when_persisting_fails()
    {
        Mail::fake();

        Projectionist::addProjector(AccountProjector::class);
        Projectionist::addReactor(SendMailReactor::class);

        $aggregateRoot = AccountAggregateRootWithFailingPersist::retrieve($this->aggregateUuid)->addMoney(123);

        $this->assertExceptionThrown(
            fn () => AggregateRoot::persistInTransaction($aggregateRoot)
        );

        $this->assertCount(0, EloquentStoredEvent::get());
        $this->assertCount(0, Account::get());
        Mail::assertNothingSent();
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

    /** @test */
    public function it_will_throw_an_exception_if_the_latest_stored_version_id_is_not_what_we_expect()
    {
        $aggregateRoot = AccountAggregateRoot::retrieve($this->aggregateUuid);
        $aggregateRoot->addMoney(100);

        $aggregateRootInAnotherRequest = AccountAggregateRoot::retrieve($this->aggregateUuid);
        $aggregateRootInAnotherRequest->addMoney(100);
        $aggregateRootInAnotherRequest->persist();

        $this->expectException(CouldNotPersistAggregate::class);
        $aggregateRoot->persist();
    }

    /** @test */
    public function it_can_allow_to_be_persisted_from_concurrent_events()
    {
        $aggregateRoot = AccountAggregateRootThatAllowsConcurrency::retrieve($this->aggregateUuid);
        $aggregateRoot->addMoney(100);

        $aggregateRootInAnotherRequest = AccountAggregateRootThatAllowsConcurrency::retrieve($this->aggregateUuid);
        $aggregateRootInAnotherRequest->addMoney(100);
        $aggregateRootInAnotherRequest->persist();

        /** This line will now not throw an exception */
        $aggregateRoot->persist();

        $this->assertTestPassed();
    }

    /** @test */
    public function it_fires_the_triggered_events_on_the_event_bus_when_configured()
    {
        config()->set('event-sourcing.dispatch_events_from_aggregate_roots', true);

        Event::fake([
            MoneyAdded::class,
        ]);

        AccountAggregateRoot::retrieve($this->aggregateUuid)
            ->addMoney(100)
            ->persist();

        Event::assertDispatched(MoneyAdded::class, function (MoneyAdded $event) {
            $this->assertEquals(100, $event->amount);
            $this->assertTrue($event->firedFromAggregateRoot);

            return true;
        });
    }

    /** @test */
    public function when_an_apply_method_is_public_it_can_have_additional_dependencies()
    {
        config()->set('event-sourcing.dispatch_events_from_aggregate_roots', true);

        Event::fake([
            MoneyMultiplied::class,
        ]);

        AccountAggregateRoot::retrieve($this->aggregateUuid)
            ->multiplyMoney(100)
            ->persist();

        Event::assertDispatched(MoneyMultiplied::class);
    }
  
    public function it_can_load_the_uuid()
    {
        $aggregateRoot = (new AccountAggregateRoot())->loadUuid($this->aggregateUuid);

        $this->assertEquals($this->aggregateUuid, $aggregateRoot->uuid());
    }

    /** @test */
    public function it_persists_when_uuid_is_loaded()
    {
        (new AccountAggregateRoot())
            ->loadUuid($this->aggregateUuid)
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
}
