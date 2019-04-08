<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Exceptions\CouldNotResetProjector;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ResettableProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ProjectorThatWritesMetaData;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ProjectThatHandlesASingleEvent;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ProjectorWithAssociativeAndNonAssociativeHandleEvents;

final class ProjectorTest extends TestCase
{
    /** @test */
    public function it_can_reach_the_stored_event_and_write_meta_data_to_it()
    {
        Projectionist::addProjector(ProjectorThatWritesMetaData::class);

        event(new MoneyAddedEvent(Account::create(), 1234));

        $this->assertCount(1, StoredEvent::get());

        $this->assertEquals(1, StoredEvent::first()->meta_data['user_id']);
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
    public function it_will_throw_an_exception_if_it_does_not_have_the_needed_method_to_reset()
    {
        $this->expectException(CouldNotResetProjector::class);

        (new BalanceProjector())->reset();
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
}
