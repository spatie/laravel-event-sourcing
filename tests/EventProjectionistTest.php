<?php

namespace Spatie\EventProjector\Tests;

use Mockery;
use Exception;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\WildcardProjector;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ProjectorThatThrowsAnException;
use Spatie\EventProjector\Tests\TestClasses\Projectors\InvalidProjectorThatCannotHandleEvents;
use Spatie\EventProjector\Tests\TestClasses\Projectors\InvalidProjectorThatDoesNotHaveTheRightEventHandlingMethod;

class EventProjectionistTest extends TestCase
{
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = Account::create();
    }

    /** @test */
    public function it_will_throw_an_exception_when_trying_to_add_a_non_existing_projector()
    {
        $this->expectException(InvalidEventHandler::class);

        EventProjectionist::addProjector('non-exising-class-name');
    }

    /** @test */
    public function it_will_thrown_an_exception_when_trying_to_add_a_non_existing_reactor()
    {
        $this->expectException(InvalidEventHandler::class);

        EventProjectionist::addReactor('non-exising-class-name');
    }

    /** @test */
    public function it_will_thrown_an_exception_when_an_event_handler_does_not_have_the_expected_event_handling_method()
    {
        $this->expectException(InvalidEventHandler::class);

        EventProjectionist::addProjector(InvalidProjectorThatDoesNotHaveTheRightEventHandlingMethod::class);

        event(new MoneyAdded($this->account, 1234));
    }

    /** @test */
    public function it_will_update_the_projector_status()
    {
        $projector = new BalanceProjector();

        EventProjectionist::addProjector($projector);

        event(new MoneyAdded($this->account, 1234));
        $this->assertTrue($projector->hasReceivedAllEvents());

        event(new MoneyAdded($this->account, 1234));
        $this->assertTrue($projector->hasReceivedAllEvents());

        $status = ProjectorStatus::getForProjector($projector);
        $this->assertEquals(2, $status->last_processed_event_id);
    }

    /** @test */
    public function it_can_handle_a_wildcard_in_handlesEvents()
    {
        EventProjectionist::addProjector(WildcardProjector::class);

        event(new MoneyAdded($this->account, 1234));

        $this->assertEquals(1234, $this->account->refresh()->amount);
    }

    /** @test */
    public function it_will_not_let_the_projector_handle_an_event_if_the_projector_hasnt_received_all_events()
    {
        $projector = new BalanceProjector();

        EventProjectionist::addProjector($projector);

        event(new MoneyAdded($this->account, 1000));

        $this->assertTrue($projector->hasReceivedAllEvents());

        $this->assertEquals(1000, $this->account->refresh()->amount);

        // manually store a new event so projectors won't get called
        StoredEvent::createForEvent(new MoneyAdded($this->account, 1000));

        $this->assertFalse($projector->hasReceivedAllEvents());

        event(new MoneyAdded($this->account, 1000));
        $this->assertEquals(1000, $this->account->refresh()->amount);
    }

    /** @test */
    public function it_will_not_register_the_same_projector_twice()
    {
        EventProjectionist::addProjector(BalanceProjector::class);
        EventProjectionist::addProjector(BalanceProjector::class);

        $this->assertCount(1, EventProjectionist::getProjectors());
    }

    /** @test */
    public function it_will_not_register_the_same_reactor_twice()
    {
        EventProjectionist::addReactor(BrokeReactor::class);
        EventProjectionist::addReactor(BrokeReactor::class);

        $this->assertCount(1, EventProjectionist::getReactors());
    }

    /** @test */
    public function it_will_call_the_method_on_the_projector_when_the_projector_throws_an_exception()
    {
        $this->setConfig('event-projector.catch_exceptions', true);

        $projector = Mockery::mock(ProjectorThatThrowsAnException::class.'[handleException]');

        $projector->shouldReceive('handleException')->once();

        EventProjectionist::addProjector($projector);

        event(new MoneyAdded($this->account, 1000));
    }

    /** @test */
    public function it_can_catch_exceptions_and_still_continue_calling_other_projectors()
    {
        $this->setConfig('event-projector.catch_exceptions', true);

        $failingProjector = new ProjectorThatThrowsAnException();
        EventProjectionist::addProjector($failingProjector);

        $workingProjector = new BalanceProjector();
        EventProjectionist::addProjector($workingProjector);

        event(new MoneyAdded($this->account, 1000));

        $this->assertEquals(0, ProjectorStatus::getForProjector($failingProjector)->last_processed_event_id);

        $this->assertEquals(1, ProjectorStatus::getForProjector($workingProjector)->last_processed_event_id);
        $this->assertEquals(1000, $this->account->refresh()->amount);
    }

    /** @test */
    public function it_can_not_catch_exceptions_and_not_continue()
    {
        $failingProjector = new ProjectorThatThrowsAnException();
        EventProjectionist::addProjector($failingProjector);

        $this->expectException(Exception::class);

        event(new MoneyAdded($this->account, 1000));
    }
}
