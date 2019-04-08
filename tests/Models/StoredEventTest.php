<?php

namespace Spatie\EventProjector\Tests\Models;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Exceptions\InvalidStoredEvent;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

final class StoredEventTest extends TestCase
{
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

    public function setUp(): void
    {
        parent::setUp();

        Projectionist::addProjector(new BalanceProjector());

        $this->account = Account::create();
    }

    /** @test */
    public function it_has_a_scope_to_get_all_events_starting_from_given_id()
    {
        $this->fireEvents(4);

        $this->assertEquals([3, 4], StoredEvent::startingFrom(3)->pluck('id')->toArray());
    }

    /** @test */
    public function it_will_throw_a_human_readable_exception_when_the_event_couldnt_be_deserialized()
    {
        $this->fireEvents();

        // sneakily change the stored event class
        StoredEvent::first()->update(['event_class' => 'NonExistingClass']);

        $this->expectException(InvalidStoredEvent::class);

        StoredEvent::first()->event;
    }

    public function fireEvents(int $number = 1, string $className = MoneyAddedEvent::class)
    {
        foreach (range(1, $number) as $i) {
            event(new $className($this->account, 1234));
        }
    }
}
