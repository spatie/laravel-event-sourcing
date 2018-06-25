<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Exceptions\CouldNotResetProjector;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ResettableProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ProjectorThatWritesMetaData;

class ProjectorTest extends TestCase
{
    /** @test */
    public function it_can_reach_the_stored_event_and_write_meta_data_to_it()
    {
        EventProjectionist::addProjector(ProjectorThatWritesMetaData::class);

        event(new MoneyAdded(Account::create(), 1234));

        $this->assertCount(1, StoredEvent::get());

        $this->assertEquals(1, StoredEvent::first()->meta_data['user_id']);
    }

    /** @test */
    public function it_can_be_reset()
    {
        $projector = new ResettableProjector();

        EventProjectionist::addProjector($projector);

        ProjectorStatus::getForProjector($projector);

        $this->assertCount(1, ProjectorStatus::get());

        $projector->reset();

        $this->assertCount(0, ProjectorStatus::get());
    }

    /** @test */

    /*
    public function a_stream_based_projector_can_be_reset()
    {
        $projector = new StreambasedProjector();

        EventProjectionist::addProjector($projector);

        event(new StreamableMoneyAdded(Account::create(), 1000));
        event(new StreamableMoneyAdded(Account::create(), 1000));

        $this->assertCount(2, ProjectorStatus::get());

        $projector->reset();

        $this->assertCount(0, ProjectorStatus::get());
    }
    */

    /** @test */
    public function it_will_throw_an_exception_if_it_does_not_have_the_needed_method_to_reset()
    {
        $this->expectException(CouldNotResetProjector::class);

        (new BalanceProjector())->reset();
    }
}
