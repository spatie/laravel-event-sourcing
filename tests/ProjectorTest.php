<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorThatWritesMetaData;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithAssociativeAndNonAssociativeHandleEvents;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithoutHandlesEvents;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectThatHandlesASingleEvent;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ResettableProjector;

class ProjectorTest extends TestCase
{
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
}
