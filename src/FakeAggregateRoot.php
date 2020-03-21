<?php

namespace Spatie\EventSourcing;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert;

class FakeAggregateRoot
{
    private AggregateRoot $aggregateRoot;

    public function __construct(AggregateRoot $aggregateRoot)
    {
        $this->aggregateRoot = $aggregateRoot;
    }

    /**
     * @param \Spatie\EventSourcing\ShouldBeStored|\Spatie\EventSourcing\ShouldBeStored[] $events
     *
     * @return $this
     */
    public function given($events): self
    {
        $events = Arr::wrap($events);

        foreach ($events as $event) {
            $this->aggregateRoot->recordThat($event);
        }

        $this->aggregateRoot->persist();

        return $this;
    }

    public function when($callable): self
    {
        $callable($this->aggregateRoot);

        return $this;
    }

    public function assertNothingRecorded(): self
    {
        Assert::assertCount(0, $this->aggregateRoot->getRecordedEvents());

        return $this;
    }

    /**
     * @param \Spatie\EventSourcing\ShouldBeStored|\Spatie\EventSourcing\ShouldBeStored[] $expectedEvents
     *
     * @return $this
     */
    public function assertRecorded($expectedEvents): self
    {
        $expectedEvents = Arr::wrap($expectedEvents);

        Assert::assertEquals($expectedEvents, $this->aggregateRoot->getRecordedEvents());

        return $this;
    }

    public function assertNotRecorded($unexpectedEventClasses): void
    {
        $actualEventClasses = array_map(fn (ShouldBeStored $event) => get_class($event), $this->aggregateRoot->getRecordedEvents());

        $unexpectedEventClasses = Arr::wrap($unexpectedEventClasses);

        foreach ($unexpectedEventClasses as $nonExceptedEventClass) {
            Assert::assertNotContains($nonExceptedEventClass, $actualEventClasses, "Did not expect to record {$nonExceptedEventClass}, but it was recorded.");
        }
    }

    public function __call($name, $arguments): self
    {
        $this->aggregateRoot->$name(...$arguments);

        return $this;
    }

    public function aggregateRoot(): AggregateRoot
    {
        return $this->aggregateRoot;
    }
}
