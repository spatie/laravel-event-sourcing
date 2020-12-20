<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEventWithQueueOverride;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\AttributeProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorThatWritesMetaData;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithAssociativeAndNonAssociativeHandleEvents;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithoutHandlesEvents;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectThatHandlesASingleEvent;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ResettableProjector;

class ProjectorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        AttributeProjector::$handledEvents = [];
    }

    /** @test */
    public function it_can_reach_the_stored_event_and_write_meta_data_to_it()
    {
        Projectionist::addProjector(ProjectorThatWritesMetaData::class);

        event(new MoneyAddedEvent(Account::create(), 1234));

        $this->assertCount(1, EloquentStoredEvent::get());

        $this->assertEquals(1, EloquentStoredEvent::first()->meta_data['user_id']);
    }

    /** @test */
    public function it_can_be_reset()
    {
        Account::create();

        $projector = new ResettableProjector();

        Projectionist::addProjector($projector);

        $this->assertCount(1, Account::all());

        $projector->reset();

        $this->assertCount(0, Account::all());
    }

    /** @test */
    public function it_can_handle_non_associative_handle_events()
    {
        $account = Account::create();

        $projector = new ProjectorWithAssociativeAndNonAssociativeHandleEvents();

        Projectionist::addProjector($projector);

        event(new MoneyAddedEvent($account, 1234));

        $this->assertEquals(1234, $account->refresh()->amount);
    }

    /** @test */
    public function it_can_handle_mixed_handle_events()
    {
        $account = Account::create();

        $projector = new ProjectorWithAssociativeAndNonAssociativeHandleEvents();

        Projectionist::addProjector($projector);

        event(new MoneyAddedEvent($account, 1234));

        event(new MoneySubtractedEvent($account, 4321));

        $this->assertEquals(-3087, $account->refresh()->amount);
    }

    /** @test */
    public function it_can_handle_a_single_event()
    {
        $account = Account::create();

        $projector = new ProjectThatHandlesASingleEvent();

        Projectionist::addProjector($projector);

        event(new MoneyAddedEvent($account, 1234));

        $this->assertEquals(1234, $account->refresh()->amount);
    }

    /** @test */
    public function it_can_find_the_right_method_for_the_right_event_without_the_need_to_specify_handles_events()
    {
        $account = Account::create();

        Projectionist::addProjector(ProjectorWithoutHandlesEvents::class);

        event(new MoneyAddedEvent($account, 1234));
        $this->assertCount(1, EloquentStoredEvent::get());
        $this->assertEquals(1234, $account->refresh()->amount);

        event(new MoneySubtractedEvent($account, 34));
        $this->assertCount(2, EloquentStoredEvent::get());
        $this->assertEquals(1200, $account->refresh()->amount);
    }

    /** @test */
    public function it_uses_attributes_to_route_events()
    {
        $account = Account::create();

        Projectionist::addProjector(AttributeProjector::class);

        event(new MoneyAddedEvent($account, 1234));

        $this->assertEquals([
            MoneyAddedEvent::class,
        ], array_keys(AttributeProjector::$handledEvents));
    }

    /** @test */
    public function it_can_use_multiple_attributes_on_the_same_method()
    {
        $account = Account::create();

        Projectionist::addProjector(AttributeProjector::class);

        event(new MoneySubtractedEvent($account, 10));
		event(new MoneyAddedEventWithQueueOverride($account, 10));
		event(new MoneyAddedEvent($account, 10));

        $this->assertEquals([
            MoneySubtractedEvent::class,
            MoneyAddedEventWithQueueOverride::class,
			MoneyAddedEvent::class,
        ], array_keys(AttributeProjector::$handledEvents));

		$this->assertTrue(AttributeProjector::$multiAttributeEventHandled);
    }

	/** @test */
	public function it_can_use_multiple_methods_for_one_event()
	{
		AttributeProjector::$singleAttributeEventHandled = false;
		AttributeProjector::$multiAttributeEventHandled = false;

		$account = Account::create();

		Projectionist::addProjector(AttributeProjector::class);

		event(new MoneyAddedEvent($account, 10));

		$this->assertTrue(AttributeProjector::$singleAttributeEventHandled);
		$this->assertTrue(AttributeProjector::$multiAttributeEventHandled);

	}
}
