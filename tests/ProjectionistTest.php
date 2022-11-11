<?php

namespace Spatie\EventSourcing\Tests;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Queue;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\HandleStoredEventJob;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\FakeMoneyAddedCountProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\MoneyAddedCountProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorThatThrowsAnException;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithHighWeight;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithLowWeight;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithNegativeWeight;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithoutWeight;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\QueuedProjector;
use Spatie\EventSourcing\Tests\TestClasses\ProjectorWithWeightTestHelper;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertSame;

beforeEach(function () {
    $this->account = Account::create();
});

it('will throw an exception when trying to add a non existing projector', function () {
    Projectionist::addProjector('non-exising-class-name');
})->throws(BindingResolutionException::class);

it('will thrown an exception when trying to add a non existing reactor', function () {
    Projectionist::addReactor('non-exising-class-name');
})->throws(BindingResolutionException::class);

it('will not register the same projector twice', function () {
    Projectionist::addProjector(BalanceProjector::class);
    Projectionist::addProjector(BalanceProjector::class);

    assertCount(1, Projectionist::getProjectors());
});

it('will not register the same reactor twice', function () {
    Projectionist::addReactor(BrokeReactor::class);
    Projectionist::addReactor(BrokeReactor::class);

    assertCount(1, Projectionist::getReactors());
});

it('will call the method on the projector when the projector throws an exception', function () {
    ProjectorThatThrowsAnException::$exceptionsHandled = 0;

    $this->setConfig('event-sourcing.catch_exceptions', true);

    Projectionist::addProjector(ProjectorThatThrowsAnException::class);

    event(new MoneyAddedEvent($this->account, 1000));

    assertEquals(1, ProjectorThatThrowsAnException::$exceptionsHandled);
});

it('will call projectors ordered by weight', function () {
    app()->singleton(ProjectorWithWeightTestHelper::class);

    Projectionist::addProjector(ProjectorWithHighWeight::class);
    Projectionist::addProjector(ProjectorWithoutWeight::class);
    Projectionist::addProjector(ProjectorWithNegativeWeight::class);
    Projectionist::addProjector(ProjectorWithLowWeight::class);

    event(new MoneyAddedEvent($this->account, 1000));

    assertSame([
        ProjectorWithNegativeWeight::class,
        ProjectorWithoutWeight::class,
        ProjectorWithLowWeight::class,
        ProjectorWithHighWeight::class,
    ], app(ProjectorWithWeightTestHelper::class)->calledBy);
});

it('can catch exceptions and still continue calling other projectors', function () {
    $this->setConfig('event-sourcing.catch_exceptions', true);

    $failingProjector = new ProjectorThatThrowsAnException();
    Projectionist::addProjector($failingProjector);

    $workingProjector = new BalanceProjector();
    Projectionist::addProjector($workingProjector);

    event(new MoneyAddedEvent($this->account, 1000));

    assertEquals(1000, $this->account->refresh()->amount);
});

it('can not catch exceptions and not continue', function () {
    $failingProjector = new ProjectorThatThrowsAnException();
    Projectionist::addProjector($failingProjector);

    event(new MoneyAddedEvent($this->account, 1000));
})->throws(Exception::class);

it('should handle projectors that dont handle fired events', function () {
    Projectionist::addProjector(MoneyAddedCountProjector::class);

    event(new MoneySubtractedEvent($this->account, 500));

    assertEquals(0, $this->account->fresh()->addition_count);
});

it('propagates custom event tags to event job', function () {
    Queue::fake();

    Projectionist::addProjector(QueuedProjector::class);

    event(new MoneyAddedEvent($this->account, 500));

    Queue::assertPushed(HandleStoredEventJob::class, function (HandleStoredEventJob $job) {
        $expected = [
            'Account:'.$this->account->id,
            MoneyAddedEvent::class,
        ];

        return $expected === $job->tags();
    });
});

it('can remove all event handlers', function () {
    Projectionist::addProjector(MoneyAddedCountProjector::class);
    Projectionist::addProjector(BalanceProjector::class);
    Projectionist::addReactor(BrokeReactor::class);

    assertCount(2, Projectionist::getProjectors());
    assertCount(1, Projectionist::getReactors());

    Projectionist::withoutEventHandlers();

    assertCount(0, Projectionist::getProjectors());
    assertCount(0, Projectionist::getReactors());
});

it('can remove certain event handlers', function () {
    Projectionist::addProjector(MoneyAddedCountProjector::class);
    Projectionist::addProjector(BalanceProjector::class);
    Projectionist::addReactor(BrokeReactor::class);

    assertCount(2, Projectionist::getProjectors());
    assertCount(1, Projectionist::getReactors());

    Projectionist::withoutEventHandlers([MoneyAddedCountProjector::class, BrokeReactor::class]);

    assertCount(1, Projectionist::getProjectors());
    assertInstanceOf(BalanceProjector::class, Projectionist::getProjectors()->first());
    assertCount(0, Projectionist::getReactors());

    Projectionist::withoutEventHandler(BalanceProjector::class);
    assertCount(0, Projectionist::getProjectors());
});

it('can fake event handlers', function () {
    FakeMoneyAddedCountProjector::$eventsHandledCount = 0;

    Projectionist::addProjector(MoneyAddedCountProjector::class);

    Projectionist::fake(MoneyAddedCountProjector::class, FakeMoneyAddedCountProjector::class);

    assertCount(1, Projectionist::getProjectors());

    event(new MoneyAddedEvent($this->account, 500));

    assertEquals(1, FakeMoneyAddedCountProjector::$eventsHandledCount);
});
