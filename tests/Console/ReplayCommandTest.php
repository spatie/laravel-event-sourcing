<?php

namespace Spatie\EventSourcing\Tests\Console;

use BadMethodCallException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

use function PHPUnit\Framework\assertEquals;

use Ramsey\Uuid\Uuid;
use Spatie\EventSourcing\Events\FinishedEventReplay;
use Spatie\EventSourcing\Events\StartingEventReplay;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRoot;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\AccountAggregateRootWithStoredEventRepositorySpecified;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\Projectors\AccountProjector;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\FakeUuid;
use Spatie\EventSourcing\Tests\TestClasses\Mailables\AccountBroke;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Models\OtherEloquentStoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;

beforeEach(function () {
    $this->account = Account::create();

    foreach (range(1, 3) as $i) {
        event(new MoneyAddedEvent($this->account, 1000));
    }

    Mail::fake();
});

it('will run without confirmation when given the force option', function () {
    Projectionist::addProjector(BalanceProjector::class);

    $this->artisan('event-sourcing:replay', ['--force' => true])
        ->expectsOutput('Replaying 3 events...')
        ->assertExitCode(0);
});

it('will not run without confirmation when not given the force option', function () {
    $this->expectException(BadMethodCallException::class);

    Projectionist::addProjector(BalanceProjector::class);

    $this->artisan('event-sourcing:replay');
});

it('will not replay events when the user does not confirm', function () {
    Projectionist::addProjector(BalanceProjector::class);

    $this->artisan('event-sourcing:replay')
        ->expectsConfirmation('Are you sure you want to replay events to all projectors?', 'no')
        ->expectsOutput('No events replayed!')
        ->assertExitCode(0);
});

it('will replay events to the given projectors', function () {
    Event::fake([FinishedEventReplay::class, StartingEventReplay::class]);

    Projectionist::addProjector(BalanceProjector::class);

    Event::assertNotDispatched(StartingEventReplay::class);
    Event::assertNotDispatched(FinishedEventReplay::class);

    $this->artisan("event-sourcing:replay \\\\Spatie\\\\EventSourcing\\\\Tests\\\\TestClasses\\\\Projectors\\\\BalanceProjector");

    Event::assertDispatched(StartingEventReplay::class);
    Event::assertDispatched(FinishedEventReplay::class);
});

it('should ask if it should run events against all of them if no projectors are given', function () {
    Projectionist::addProjector(BalanceProjector::class);

    $this->artisan('event-sourcing:replay')
        ->expectsQuestion('Are you sure you want to replay events to all projectors?', 'Y')
        ->expectsOutput('Replaying 3 events...')
        ->assertExitCode(0);
});

it('can replay events starting from a specific number', function () {
    $projectorClass = BalanceProjector::class;

    Projectionist::addProjector($projectorClass);

    $this->artisan('event-sourcing:replay', ['projector' => [BalanceProjector::class], '--from' => 2])
        ->expectsOutput('Replaying 2 events...')
        ->assertExitCode(0);
});

it('will not call any reactors', function () {
    Projectionist::addProjector(BalanceProjector::class);
    Projectionist::addReactor(BrokeReactor::class);

    EloquentStoredEvent::truncate();

    $account = Account::create();
    event(new MoneySubtractedEvent($account, 2000));

    Mail::assertSent(AccountBroke::class, 1);

    Account::create();

    Artisan::call('event-sourcing:replay', ['projector' => [BalanceProjector::class]]);

    Mail::assertSent(AccountBroke::class, 1);
});

it('will call certain methods on the projector when replaying events', function () {
    BalanceProjector::$log = [];

    $projector = app(BalanceProjector::class);

    Projectionist::addProjector($projector);

    Artisan::call('event-sourcing:replay', [
        'projector' => [get_class($projector)],
    ]);

    assertEquals([
        'onStartingEventReplay',
        MoneyAddedEvent::class,
        MoneyAddedEvent::class,
        MoneyAddedEvent::class,
        'onFinishedEventReplay',
    ], BalanceProjector::$log);
});

it('will replay events from a specific store', function () {
    $uuid = FakeUuid::generate();

    foreach (range(1, 5) as $i) {
        AccountAggregateRootWithStoredEventRepositorySpecified::retrieve($uuid)
            ->addMoney(2000)
            ->persist();
    };

    $projector = app(AccountProjector::class);
    Projectionist::addProjector($projector);

    $this->artisan('event-sourcing:replay', [
        'projector' => [AccountProjector::class],
        '--stored-event-model' => OtherEloquentStoredEvent::class,
    ])
        ->expectsOutput('Replaying 5 events...')
        ->assertExitCode(0);
});

it('will replay events for a specific aggregate root uuid', function () {
    EloquentStoredEvent::truncate();

    $uuid1 = Uuid::uuid4();
    $account1 = AccountAggregateRoot::retrieve($uuid1);
    $account1->addMoney(1000);
    $account1->persist();

    $uuid2 = Uuid::uuid4();
    $account2 = AccountAggregateRoot::retrieve($uuid2);
    $account2->addMoney(1000);
    $account2->persist();

    $projector = app(AccountProjector::class);

    Projectionist::addProjector($projector);

    $this->artisan('event-sourcing:replay', [
        'projector' => [AccountProjector::class],
        '--aggregate-uuid' => $uuid1,
    ])
        ->expectsOutput('Replaying 1 events...')
        ->assertExitCode(0);
});
