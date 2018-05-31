<?php

namespace Spatie\EventProjector\Console;

use Mockery;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Events\FinishedReplayingAllEvents;
use Spatie\EventProjector\Events\StartingReplayingAllEvents;
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
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = Account::create();

        foreach (range(1, 3) as $i) {
            event(new MoneyAdded($this->account, 1000));
        }

        Mail::fake();
    }

    /** @test */
    public function it_will_replay_events_to_the_given_projectors()
    {
        Event::fake([FinishedReplayingAllEvents::class, StartingReplayingAllEvents::class]);

        $projector = Mockery::mock(BalanceProjector::class.'[onMoneyAdded]');

        $projector->shouldReceive('onMoneyAdded')->andReturnNull()->times(3);

        EventProjectionist::addProjector($projector);

        Event::assertNotDispatched(StartingReplayingAllEvents::class);
        Event::assertNotDispatched(FinishedReplayingAllEvents::class);

        $this->artisan('event-projector:replay-events', ['--projector' => [get_class($projector)]]);

        Event::assertDispatched(StartingReplayingAllEvents::class);
        Event::assertDispatched(FinishedReplayingAllEvents::class);
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

        $this->assertSeeInConsoleOutput('Replaying all events...');
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

    /** @test */
    public function it_can_only_replay_events_that_the_projector_did_not_handle_yet()
    {
        $projector = new BalanceProjector();

        EventProjectionist::addProjector($projector);

        //sneakely change the last processed event
        ProjectorStatus::getForProjector($projector)->rememberLastProcessedEvent(StoredEvent::find(2));

        $this->artisan('event-projector:replay-events', [
            '--projector' => [BalanceProjector::class],
            '--only-new-events' => '',
        ]);

        $this->assertSeeInConsoleOutput('Replaying events after stored event id 2...');

        // only one event is replayed
        $this->assertEquals(1000, $this->account->fresh()->amount);
    }

    /** @test */
    public function it_will_call_certain_methods_on_the_projector_when_replaying_all_events()
    {
        $projector = Mockery::mock(BalanceProjector::class.'[onStartingReplayingAllEvents, onFinishedReplayingAllEvents]');

        EventProjectionist::addProjector($projector);

        $projector->shouldReceive('onStartingReplayingAllEvents')->once();
        $projector->shouldReceive('onFinishedReplayingAllEvents')->once();

        $this->artisan('event-projector:replay-events', [
            '--projector' => [get_class($projector)],
        ]);
    }
}
