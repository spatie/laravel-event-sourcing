<?php

namespace Spatie\EventProjector\Console;

use Mockery;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Events\FinishedEventReplay;
use Spatie\EventProjector\Events\StartingEventReplay;
use Spatie\EventProjector\Facades\EventProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;
use Spatie\EventProjector\Tests\TestClasses\Mailables\AccountBroke;
use Spatie\EventProjector\EventProjectionist as BoundEventProjectionist;
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
        $projector = Mockery::mock(BalanceProjector::class.'[onMoneyAdded]');

        $projector->shouldReceive('onMoneyAdded')->andReturnNull()->times(3);

        EventProjectionist::addProjector($projector);

        Event::assertNotDispatched(StartingEventReplay::class);
        Event::assertNotDispatched(FinishedEventReplay::class);

        $this->artisan('event-projector:replay-events', ['--projector' => [get_class($projector)]]);

        Event::assertDispatched(StartingEventReplay::class);
        Event::assertDispatched(FinishedEventReplay::class);
    }

    /** @test */
    public function if_no_projectors_are_given_it_will_ask_if_it_should_run_events_againts_all_of_them()
    {
        EventProjectionist::addProjector(BalanceProjector::class);

        $command = Mockery::mock(ReplayEventsCommand::class.'[confirm]', [
            app(BoundEventProjectionist::class),
            config('event-projector.stored_event_model'),
        ]);

        $command->shouldReceive('confirm')->andReturn(false);

        $this->app->bind('command.event-projector:replay-events', function () use ($command) {
            return $command;
        });

        $this->artisan('event-projector:replay-events');

        $this->assertSeeInConsoleOutput('No events replayed!');
    }

    /** @test */
    public function it_will_run_events_agains_all_projectors_when_no_projectors_are_given_and_confirming()
    {
        EventProjectionist::addProjector(BalanceProjector::class);

        $command = Mockery::mock(ReplayEventsCommand::class.'[confirm]', [
            app(BoundEventProjectionist::class),
            config('event-projector.stored_event_model'),
        ]);

        $command->shouldReceive('confirm')->andReturn(true);

        $this->app->bind('command.event-projector:replay-events', function () use ($command) {
            return $command;
        });

        $this->artisan('event-projector:replay-events');

        $this->assertSeeInConsoleOutput('Replaying events...');
    }

    /** @test */
    public function it_will_not_call_any_reactors()
    {
        EventProjectionist::addProjector(BalanceProjector::class);
        EventProjectionist::addReactor(BrokeReactor::class);

        StoredEvent::truncate();

        $account = Account::create();
        event(new MoneySubtracted($account, 2000));

        Mail::assertSent(AccountBroke::class, 1);

        Account::create();

        $this->artisan('event-projector:replay-events', ['--projector' => [BalanceProjector::class]]);

        Mail::assertSent(AccountBroke::class, 1);
    }
}
