<?php

namespace Spatie\EventSaucer\Tests;

use Spatie\EventSaucer\LoggedEvent;
use Spatie\EventSaucer\Tests\Models\Account;
use Spatie\EventSaucer\Tests\Events\MoneyAdded;

class EventSubscriberTest extends TestCase
{
    /** @var \Spatie\EventSaucer\Tests\Models\Account */
    protected $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = new Account();
    }

    /** @test */
    public function it_will_log_events()
    {
        event(new MoneyAdded($this->account, 1234));

        $this->assertCount(1, LoggedEvent::all());
    }
}