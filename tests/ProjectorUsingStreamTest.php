<?php

namespace Spatie\EventProjector\Tests;

use Illuminate\Support\Facades\Event;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Events\ProjectorDidNotHandlePriorEvents;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ProjectorThatUsesStreamDefinedByArray;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ProjectorThatUsesStreamDefinedByString;

class ProjectorUsingStreamTest extends TestCase
{
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

    public function setUp()
    {
        parent::setUp();

        Event::fake([ProjectorDidNotHandlePriorEvents::class]);

        $this->account = Account::create();
    }

    /**
     * @test
     *
     * @dataProvider projectorProvider
     *
     * @param string $projectorClass
     */
    public function it_will_track_events_using_the_stream_name(string $projectorClass)
    {
        EventProjectionist::addProjector($projectorClass);

        event(new MoneyAdded($this->account, 1000));

        $this->assertCount(1, StoredEvent::get());

        $this->assertDatabaseHas('projector_statuses', [
            'projector_name' => $projectorClass,
            'stream' => "account.id-{$this->account->id}",
        ]);
    }

    /**
     * @test
     *
     * @dataProvider projectorProvider
     *
     * @param string $projectorClass
     */
    public function it_will_not_accept_new_events_if_the_stream_is_not_up_to_date(string $projectorClass)
    {
        EventProjectionist::addProjector($projectorClass);

        event(new MoneyAdded($this->account, 1000));
        $this->assertEquals(1000, $this->account->refresh()->amount);

        // simulate that the event hasn't been received yet
        ProjectorStatus::truncate();
        $this->account->update(['amount' => 0]);

        event(new MoneyAdded($this->account, 1000));

        //projectorClass not up to date for that account stream, still old amount
        $this->assertEquals(0, $this->account->refresh()->amount);
        Event::assertDispatched(ProjectorDidNotHandlePriorEvents::class);

        // other accounts still get updated
        $otherAccount = Account::create();
        event(new MoneyAdded($otherAccount, 1000));
        $this->assertEquals(1000, $otherAccount->refresh()->amount);
        event(new MoneyAdded($otherAccount, 1000));
        $this->assertEquals(2000, $otherAccount->refresh()->amount);

        // first account still won't get updated
        event(new MoneyAdded($this->account, 1000));
        $this->assertEquals(0, $this->account->refresh()->amount);

        EventProjectionist::replayEvents(collect($projectorClass));

        // all events of first account are now applied
        $this->assertEquals(2000, $otherAccount->refresh()->amount);
        $this->assertEquals(3000, $this->account->refresh()->amount);

        // projectorClass is up to date for the first account, new events will be applied
        event(new MoneyAdded($this->account, 1000));
        $this->assertEquals(4000, $this->account->refresh()->amount);
    }

    public function projectorProvider(): array
    {
        return [
            [ProjectorThatUsesStreamDefinedByString::class],
            [ProjectorThatUsesStreamDefinedByArray::class],
        ];
    }
}
