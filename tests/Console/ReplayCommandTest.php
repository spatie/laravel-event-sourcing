<?php

namespace Spatie\EventSourcing\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Spatie\EventSourcing\Events\FinishedEventReplay;
use Spatie\EventSourcing\Events\StartingEventReplay;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestCase;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRootWithStoredEventRepositorySpecified;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Mailables\AccountBroke;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Models\OtherEloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;

class ReplayCommandTest extends TestCase
{
    protected Account $account;

    public function setUp(): void
    {
        parent::setUp();

        $this->account = Account::create();

        foreach (range(1, 3) as $i) {
            event(new MoneyAddedEvent($this->account, 1000));
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

        $this->artisan('event-sourcing:replay '.get_class($projector));

        Event::assertDispatched(StartingEventReplay::class);
        Event::assertDispatched(FinishedEventReplay::class);
    }

    /** @test */
    public function if_no_projectors_are_given_it_will_ask_if_it_should_run_events_againts_all_of_them()
    {
        Projectionist::addProjector(BalanceProjector::class);

        $this->artisan('event-sourcing:replay')
            ->expectsQuestion('Are you sure you want to replay events to all projectors?', 'Y')
            ->expectsOutput('Replaying 3 events...')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_replay_events_starting_from_a_specific_number()
    {
        $projectorClass = BalanceProjector::class;

        Projectionist::addProjector($projectorClass);

        $this->artisan('event-sourcing:replay', ['projector' => [BalanceProjector::class], '--from' => 2])
            ->expectsOutput('Replaying 2 events...')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_will_not_call_any_reactors()
    {
        Projectionist::addProjector(BalanceProjector::class);
        Projectionist::addReactor(BrokeReactor::class);

        EloquentStoredEvent::truncate();

        $account = Account::create();
        event(new MoneySubtractedEvent($account, 2000));

        Mail::assertSent(AccountBroke::class, 1);

        Account::create();

        Artisan::call('event-sourcing:replay', ['projector' => [BalanceProjector::class]]);

        Mail::assertSent(AccountBroke::class, 1);
    }

    /** @test */
    public function it_will_call_certain_methods_on_the_projector_when_replaying_events()
    {
        $projector = Mockery::mock(BalanceProjector::class.'[onStartingEventReplay, onFinishedEventReplay]');

        Projectionist::addProjector($projector);

        $projector->shouldReceive('onStartingEventReplay')->once();
        $projector->shouldReceive('onFinishedEventReplay')->once();

        Artisan::call('event-sourcing:replay', [
            'projector' => [get_class($projector)],
        ]);
    }

    public function it_will_replay_events_from_a_specific_store()
    {
        $account = AccountAggregateRootWithStoredEventRepositorySpecified::create();

        foreach (range(1, 5) as $i) {
            event(new MoneyAddedEvent($account, 2000));
        }

        OtherEloquentStoredEvent::truncate();

        $this->artisan('event-sourcing:replay', ['--stored-event-model' => OtherEloquentStoredEvent::class])
            ->expectsOutput('Replaying 5 events...')
            ->assertExitCode(0);
    }
}
