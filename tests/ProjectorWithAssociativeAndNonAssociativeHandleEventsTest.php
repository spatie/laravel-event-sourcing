<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ProjectorWithAssociativeAndNonAssociativeHandleEvents;

class ProjectorWithAssociativeAndNonAssociativeHandleEventsTest extends TestCase
{
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = Account::create();
    }

    /** @test */
    public function it_can_handle_non_associative_handle_events()
    {
        $projector = new ProjectorWithAssociativeAndNonAssociativeHandleEvents();

        EventProjectionist::addProjector($projector);

        event(new MoneyAdded($this->account, 1234));

        $this->assertEquals(1234, $this->account->refresh()->amount);
    }

    /** @test */
    public function it_can_handle_mixed_handle_events()
    {
        $projector = new ProjectorWithAssociativeAndNonAssociativeHandleEvents();

        EventProjectionist::addProjector($projector);

        event(new MoneyAdded($this->account, 1234));

        event(new MoneySubtracted($this->account, 4321));

        $this->assertEquals(-3087, $this->account->refresh()->amount);
    }
}
