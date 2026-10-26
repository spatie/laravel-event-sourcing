<?php

namespace Spatie\EventSourcing\Tests;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Queue;

use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\Facades\Projectionist;
use Spatie\EventSourcing\StoredEvents\HandleStoredEventJob;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyAddedInterface;
use Spatie\EventSourcing\Tests\TestClasses\AggregateRoots\StorableEvents\MoneyIncremented;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\SpecialMoneyAddedEvent;
use Spatie\EventSourcing\Tests\TestClasses\Events\TestEvent;
use Spatie\EventSourcing\Tests\TestClasses\Models\Account;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\BalanceProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\FakeMoneyAddedCountProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\MoneyAddedCountProjector;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorThatThrowsAnException;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithDynamicWeight;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithHighWeight;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithLowWeight;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithNegativeWeight;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithoutWeight;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\ProjectorWithUnionTypeHandler;
use Spatie\EventSourcing\Tests\TestClasses\Projectors\QueuedProjector;
use Spatie\EventSourcing\Tests\TestClasses\ProjectorWithWeightTestHelper;
use Spatie\EventSourcing\Tests\TestClasses\Reactors\BrokeReactor;

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

it('will call projectors ordered by weight', function (string $eventClass, array $sortedProjectors) {
    app()->singleton(ProjectorWithWeightTestHelper::class);

    Projectionist::addProjector(ProjectorWithDynamicWeight::class);
    Projectionist::addProjector(ProjectorWithHighWeight::class);
    Projectionist::addProjector(ProjectorWithoutWeight::class);
    Projectionist::addProjector(ProjectorWithNegativeWeight::class);
    Projectionist::addProjector(ProjectorWithLowWeight::class);

    event(new $eventClass($this->account, 1000));

    assertSame($sortedProjectors, app(ProjectorWithWeightTestHelper::class)->calledBy);
})->with([
    [MoneyAddedEvent::class, [
        ProjectorWithNegativeWeight::class,
        ProjectorWithoutWeight::class,
        ProjectorWithLowWeight::class,
        ProjectorWithDynamicWeight::class,
        ProjectorWithHighWeight::class,
    ]],
    [MoneySubtractedEvent::class, [
        ProjectorWithNegativeWeight::class,
        ProjectorWithDynamicWeight::class,
        ProjectorWithoutWeight::class,
        ProjectorWithLowWeight::class,
        ProjectorWithHighWeight::class,
    ]],
]);

it('will replay projectors ordered by weight', function (string $eventClass, array $sortedProjectors) {
    app()->singleton(ProjectorWithWeightTestHelper::class);

    event(new $eventClass($this->account, 1000));

    Projectionist::addProjectors([
        ProjectorWithDynamicWeight::class,
        ProjectorWithHighWeight::class,
        ProjectorWithoutWeight::class,
        ProjectorWithNegativeWeight::class,
        ProjectorWithLowWeight::class,
    ]);

    Projectionist::replay(Projectionist::getProjectors());

    assertSame($sortedProjectors, app(ProjectorWithWeightTestHelper::class)->calledBy);
})->with([
    [MoneyAddedEvent::class, [
        ProjectorWithNegativeWeight::class,
        ProjectorWithoutWeight::class,
        ProjectorWithLowWeight::class,
        ProjectorWithDynamicWeight::class,
        ProjectorWithHighWeight::class,
    ]],
    [MoneySubtractedEvent::class, [
        ProjectorWithNegativeWeight::class,
        ProjectorWithDynamicWeight::class,
        ProjectorWithoutWeight::class,
        ProjectorWithLowWeight::class,
        ProjectorWithHighWeight::class,
    ]],
]);

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

it('does not resolve projectors from the container at registration time', function () {
    $resolved = false;

    $this->app->bind(BalanceProjector::class, function () use (&$resolved) {
        $resolved = true;

        return new BalanceProjector();
    });

    Projectionist::addProjector(BalanceProjector::class);

    assertFalse($resolved);

    Projectionist::getProjectors();

    assertTrue($resolved);
});

it('does not resolve reactors from the container at registration time', function () {
    $resolved = false;

    $this->app->bind(BrokeReactor::class, function () use (&$resolved) {
        $resolved = true;

        return new BrokeReactor();
    });

    Projectionist::addReactor(BrokeReactor::class);

    assertFalse($resolved);

    Projectionist::getReactors();

    assertTrue($resolved);
});

it('lazily registered handlers still handle events', function () {
    Projectionist::addProjector(BalanceProjector::class);

    event(new MoneyAddedEvent($this->account, 1234));

    assertEquals(1234, $this->account->refresh()->amount);
});

it('lists a union typed handler under each of its event classes', function () {
    assertEquals([
        MoneyAddedEvent::class => ['onMoneyChanged', 'onMoneyAdded'],
        MoneySubtractedEvent::class => ['onMoneyChanged'],
        TestEvent::class => ['onTestEvent'],
    ], (new ProjectorWithUnionTypeHandler())->getEventHandlingMethods()->all());
});

it('replays events handled by a union typed handler exactly once per matching method', function (bool $useClassMap) {
    if ($useClassMap) {
        config()->set('event-sourcing.event_class_map', [
            'added' => MoneyAddedEvent::class,
            'subtracted' => MoneySubtractedEvent::class,
            'test' => TestEvent::class,
        ]);
        config()->set('event-sourcing.enforce_event_class_map', true);
    }

    event(new MoneyAddedEvent($this->account, 10));
    event(new MoneySubtractedEvent($this->account, 5));
    event(new TestEvent());
    event(new MoneyAddedEvent($this->account, 17));

    ProjectorWithUnionTypeHandler::$log = [];
    $replayedIds = [];

    Projectionist::replay(
        collect([new ProjectorWithUnionTypeHandler()]),
        onEventReplayed: function (StoredEvent $event) use (&$replayedIds) {
            $replayedIds[] = $event->id;
        },
    );

    assertSame([1, 2, 3, 4], $replayedIds);
    assertSame([
        [MoneyAddedEvent::class, 10],
        ['added', 10],
        [MoneySubtractedEvent::class, 5],
        [TestEvent::class],
        [MoneyAddedEvent::class, 17],
        ['added', 17],
    ], ProjectorWithUnionTypeHandler::$log);
})->with([false, true]);

it('replays broad handlers without losing accepted events', function (Projector $projector, array $expected) {
    app()->instance($projector::class, $projector);
    Projectionist::addProjector($projector);

    event(new MoneyAddedEvent($this->account, 10));
    event(new MoneyIncremented(7));
    event(new MoneySubtractedEvent($this->account, 5));
    event(new SpecialMoneyAddedEvent($this->account, 17));
    event(new TestEvent());

    assertSame($expected, $projector->log);
    $projector->log = [];

    Projectionist::replay(collect([$projector]));

    assertSame($expected, $projector->log);
})->with([
    'object' => [fn () => new class () extends Projector {
        public array $log = [];

        public function onEvent(object $event): void
        {
            $this->log[] = $event::class;
        }
    }, [MoneyAddedEvent::class, MoneyIncremented::class, MoneySubtractedEvent::class, SpecialMoneyAddedEvent::class, TestEvent::class]],
    'object union' => [fn () => new class () extends Projector {
        public array $log = [];

        public function onEvent(object|string $event): void
        {
            $this->log[] = $event::class;
        }
    }, [MoneyAddedEvent::class, MoneyIncremented::class, MoneySubtractedEvent::class, SpecialMoneyAddedEvent::class, TestEvent::class]],
    'stored event union' => [fn () => new class () extends Projector {
        public array $log = [];

        public function onEvent(ShouldBeStored|string $event): void
        {
            $this->log[] = $event::class;
        }
    }, [MoneyAddedEvent::class, MoneyIncremented::class, MoneySubtractedEvent::class, SpecialMoneyAddedEvent::class, TestEvent::class]],
    'interface union' => [fn () => new class () extends Projector {
        public array $log = [];

        public function onEvent(MoneyAddedEvent|MoneyAddedInterface $event): void
        {
            $this->log[] = $event::class;
        }
    }, [MoneyAddedEvent::class, MoneyIncremented::class, SpecialMoneyAddedEvent::class]],
    'class union' => [fn () => new class () extends Projector {
        public array $log = [];

        protected function onEvent(MoneyAddedEvent|MoneySubtractedEvent $event): void
        {
            $this->log[] = $event::class;
        }
    }, [MoneyAddedEvent::class, MoneySubtractedEvent::class, SpecialMoneyAddedEvent::class]],
]);
