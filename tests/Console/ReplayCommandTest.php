<?php

namespace Spatie\EventProjector\Console;

use Mockery;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Events\FinishedEventReplay;
use Spatie\EventProjector\Events\StartingEventReplay;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Projectionist as BoundProjectionist;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Reactors\BrokeReactor;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtracted;
use Spatie\EventProjector\Tests\TestClasses\Mailables\AccountBroke;
use Spatie\EventProjector\Tests\TestClasses\Projectors\BalanceProjector;

class ReplayCommandTest extends TestCase
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
        Event::fake([FinishedEventReplay::class, StartingEventReplay::class]);

        $projector = Mockery::mock(BalanceProjector::class.'[onMoneyAdded]');

        $projector->shouldReceive('onMoneyAdded')->andReturnNull()->times(3);

        Projectionist::addProjector($projector);

        Event::assertNotDispatched(StartingEventReplay::class);
        Event::assertNotDispatched(FinishedEventReplay::class);

        $this->artisan('event-projector:replay', ['projector' => [get_class($projector)]]);

        Event::assertDispatched(StartingEventReplay::class);
        Event::assertDispatched(FinishedEventReplay::class);
    }

    /** @test */
    public function if_no_projectors_are_given_it_will_ask_if_it_should_run_events_againts_all_of_them()
    {
        Projectionist::addProjector(BalanceProjector::class);

        $command = Mockery::mock(ReplayCommand::class.'[confirm]', [
            app(BoundProjectionist::class),
            config('event-projector.stored_event_model'),
        ]);

        $command->shouldReceive('confirm')->andReturn(false);

        $this->app->bind('command.event-projector:replay', function () use ($command) {
            return $command;
        });

        $this->artisan('event-projector:replay');

        $this->assertSeeInConsoleOutput('No events replayed!');
    }

    /** @test */
    public function it_will_run_events_agains_all_projectors_when_no_projectors_are_given_and_confirming()
    {
        Projectionist::addProjector(BalanceProjector::class);

        $command = Mockery::mock(ReplayCommand::class.'[confirm]', [
            app(BoundProjectionist::class),
            config('event-projector.stored_event_model'),
        ]);

        $command->shouldReceive('confirm')->andReturn(true);

        $this->app->bind('command.event-projector:replay', function () use ($command) {
            return $command;
        });

        $this->artisan('event-projector:replay');

        $this->assertSeeInConsoleOutput('Replaying all events...');
    }

    /** @test */
    public function it_will_not_call_any_reactors()
    {
        Projectionist::addProjector(BalanceProjector::class);
        Projectionist::addReactor(BrokeReactor::class);

        StoredEvent::truncate();

        $account = Account::create();
        event(new MoneySubtracted($account, 2000));

        Mail::assertSent(AccountBroke::class, 1);

        Account::create();

        $this->artisan('event-projector:replay', ['projector' => [BalanceProjector::class]]);

        Mail::assertSent(AccountBroke::class, 1);
    }

    /** @test */
    public function it_can_only_replay_events_that_the_projector_did_not_handle_yet()
    {
        $projector = new BalanceProjector();

        Projectionist::addProjector($projector);

        $this->artisan('event-projector:replay', [
            'projector' => [BalanceProjector::class],
        ]);

        $projectorStatus = ProjectorStatus::getForProjector($projector);

        $projectorStatus = $projectorStatus->refresh();

        $this->assertEquals(3, $projectorStatus->last_processed_event_id);

        //sneakily change the last processed event
        $projectorStatus->rememberLastProcessedEvent(StoredEvent::find(2));
        $projectorStatus->has_received_all_events = false;
        $projectorStatus->save();

        $this->assertEquals(2, $projectorStatus->last_processed_event_id);
        $this->artisan('event-projector:replay', [
            'projector' => [BalanceProjector::class],
        ]);

        $this->assertSeeInConsoleOutput('Replaying events after stored event id 2...');

        // 3000 from the first three events + 1000 from the replay
        $this->assertEquals(4000, $this->account->fresh()->amount);
    }

    /** @test */
    public function it_will_call_certain_methods_on_the_projector_when_replaying_events()
    {
        $projector = Mockery::mock(BalanceProjector::class.'[onStartingEventReplay, onFinishedEventReplay]');

        Projectionist::addProjector($projector);

        $projector->shouldReceive('onStartingEventReplay')->once();
        $projector->shouldReceive('onFinishedEventReplay')->once();

        $this->artisan('event-projector:replay', [
            'projector' => [get_class($projector)],
        ]);
    }
}
