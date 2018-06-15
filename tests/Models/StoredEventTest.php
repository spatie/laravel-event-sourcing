<?php

namespace Spatie\EventProjector\Tests\Models;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

class StoredEventTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        EventProjectionist::addProjector(new BalanceProjector());
    }

    /** @test */
    public function it_has_a_scope_to_get_all_events_after_a_given_id()
    {
        $this->fireEvents(4);

        $this->assertEquals([3, 4], StoredEvent::after(2)->pluck('id')->toArray());
    }

    public function fireEvents(int $number = 1)
    {
        $account = Account::create();

        foreach (range(1, $number) as $i) {
            event(new MoneyAdded($account, 1234));
        }
    }

    /** @test */
    public function it_can_get_the_previous_event_in_a_stream()
    {
        $this->fireEvents(1);

        $firstStoredEvent = StoredEvent::last();

        $this->assertNull($firstStoredEvent->previousInStream());

        $this->fireEvents(1);

        $storedEvent = StoredEvent::last();

        $this->assertEquals($firstStoredEvent->id, optional($storedEvent->previousInStream())->id);
    }
}
