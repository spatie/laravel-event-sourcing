<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
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
    public function it_will_thrown_an_exception_when_trying_to_add_a_non_existing_projector()
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
    public function it_will_thrown_an_exception_when_an_event_handler_that_cannot_handle_events_gets_called()
    {
        $this->expectException(InvalidEventHandler::class);

        EventProjectionist::addProjector(InvalidProjectorThatCannotHandleEvents::class);

        event(new MoneyAdded($this->account, 1234));
    }

    /** @test */
    public function it_will_thrown_an_exception_when_an_event_handler_does_not_have_the_expected_event_handling_method()
    {
        $this->expectException(InvalidEventHandler::class);

        EventProjectionist::addProjector(InvalidProjectorThatDoesNotHaveTheRightEventHandlingMethod::class);

        event(new MoneyAdded($this->account, 1234));
    }
}
