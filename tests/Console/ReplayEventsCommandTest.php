<?php

namespace Spatie\EventProjector\Console;

use Mockery;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Events\FinishedEventReplay;
use Spatie\EventProjector\Events\StartingEventReplay;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;
use Spatie\EventProjector\Tests\TestClasses\Mailables\AccountBroke;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

class ReplayEventsCommandTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $account = Account::create();

        foreach (range(1, 3) as $i) {
            event(new MoneyAdded($account, 1234));
        }

        Event::fake([FinishedEventReplay::class, StartingEventReplay::class]);

        Mail::fake();
    }

    /** @test */
    public function it_will_replay_events_to_the_given_projectors()
    {
        $projector = Mockery::mock(BalanceProjector::class);

        $projector->shouldReceive('onMoneyAdded')->andReturnNull()->times(3);

        EventProjectionist::addProjector($projector);

        Event::assertNotDispatched(StartingEventReplay::class);
        Event::assertNotDispatched(FinishedEventReplay::class);

        $this->artisan('event-projector:replay-events', ['--projector' => [get_class($projector)]]);

        Event::assertDispatched(StartingEventReplay::class);
        Event::assertDispatched(FinishedEventReplay::class);
    }

    /** @test */
    public function it_will_not_replay_any_events_if_there_are_no_projectors_given()
    {
        $this->artisan('event-projector:replay-events');

        $this->assertSee('No projectors found');
    }

    /** @test */
    public function it_will_not_call_any_reactors()
    {
        EventProjectionist::addProjector(BalanceProjector::class);
        EventProjectionist::addReactor(BrokeReactor::class);

        $account = Account::create();
        event(new MoneySubtracted($account, 2000));

        Mail::assertSent(AccountBroke::class, 1);

        Account::create();

        $this->artisan('event-projector:replay-events', ['--projector' => [BalanceProjector::class]]);

        Mail::assertSent(AccountBroke::class, 1);
    }

    protected function assertSee(string $text)
    {
        $this->assertContains($text, Artisan::output());
    }
}
