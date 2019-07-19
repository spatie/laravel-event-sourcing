<?php

namespace Spatie\EventProjector;

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
        foreach($events as $event) {
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

    public function assertRecorded(array $expectedEvents): void
    {
        Assert::assertEquals($expectedEvents, $this->aggregateRoot->getRecordedEvents());
    }

    public function __call($name, $arguments)
    {
        $this->aggregateRoot->$name(...$arguments);

        return $this;
    }
}

