<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert;

class FakeAggregateRoot
{
    /** @var \Spatie\EventProjector\AggregateRoot */
    private $aggregateRoot;

    public function __construct(AggregateRoot $aggregateRoot)
    {
        $this->aggregateRoot = $aggregateRoot;
    }

    public function given(array $events)
    {
        foreach ($events as $event) {
            $this->aggregateRoot->recordThat($event);
        }

        $this->aggregateRoot->persist();

        return $this;
    }

    public function when($callable)
    {
        $callable($this->aggregateRoot);

        return $this;
    }

    public function assertNothingRecorded()
    {
        Assert::assertCount(0, $this->aggregateRoot->getRecordedEvents());

        return $this;
    }

    public function assertRecorded(array $expectedEvents)
    {
        Assert::assertEquals($expectedEvents, $this->aggregateRoot->getRecordedEvents());

        return $this;
    }

    public function assertNotRecorded($unexpectedEventClasses): void
    {
        $actualEventClasses = array_map(function (ShouldBeStored $event) {
            return get_class($event);
        }, $this->aggregateRoot->getRecordedEvents());

        $unexpectedEventClasses = Arr::wrap($unexpectedEventClasses);

        foreach ($unexpectedEventClasses as $nonExceptedEventClass) {
            Assert::assertNotContains($nonExceptedEventClass, $actualEventClasses, "Did not expect to record {$nonExceptedEventClass}, but it was recorded.");
        }
    }

    public function __call($name, $arguments)
    {
        $this->aggregateRoot->$name(...$arguments);

        return $this;
    }
}
