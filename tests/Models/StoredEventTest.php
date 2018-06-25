<?php

namespace Spatie\EventProjector\Tests\Models;

use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventProjector\Tests\TestClasses\Events\Streamable\MoneyAdded as StreamableMoneyAdded;

class StoredEventTest extends TestCase
{
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

    public function setUp()
    {
        parent::setUp();

        EventProjectionist::addProjector(new BalanceProjector());

        $this->account = Account::create();
    }

    /** @test */
    public function it_has_a_scope_to_get_all_events_after_a_given_id()
    {
        $this->fireEvents(4);

        $this->assertEquals([3, 4], StoredEvent::after(2)->pluck('id')->toArray());
    }

    public function fireEvents(int $number = 1, string $className = MoneyAdded::class)
    {
        foreach (range(1, $number) as $i) {
            event(new $className($this->account, 1234));
        }
    }
}
