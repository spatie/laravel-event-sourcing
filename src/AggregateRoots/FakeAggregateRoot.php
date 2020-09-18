<?php

namespace Spatie\EventSourcing\AggregateRoots;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert;
use Spatie\EventSourcing\Enums\MetaData;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class FakeAggregateRoot
{
    private AggregateRoot $aggregateRoot;

    private $whenResult = null;

    public function __construct(AggregateRoot $aggregateRoot)
    {
        $this->aggregateRoot = $aggregateRoot;
    }

    /**
     * @param \Spatie\EventSourcing\StoredEvents\ShouldBeStored|\Spatie\EventSourcing\StoredEvents\ShouldBeStored[] $events
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
        $this->whenResult = $callable($this->aggregateRoot);

        return $this;
    }

    public function then(callable $callback): self
    {
        $result = $callback($this->whenResult);

        if ($result !== null) {
            Assert::assertTrue($result);
        }

        return $this;
    }

    public function assertNothingRecorded(): self
    {
        Assert::assertCount(0, $this->aggregateRoot->getRecordedEvents());

        return $this;
    }

    /**
     * @param \Spatie\EventSourcing\StoredEvents\ShouldBeStored|\Spatie\EventSourcing\StoredEvents\ShouldBeStored[] $expectedEvents
     *
     * @return $this
     */
    public function assertRecorded($expectedEvents): self
    {
        $expectedEvents = Arr::wrap($expectedEvents);

        $recordedEvents = $this->getRecordedEventsWithoutUuid();

        Assert::assertEquals($expectedEvents, $recordedEvents);

        return $this;
    }

    public function assertNotRecorded($unexpectedEventClasses): self
    {
        $actualEventClasses = array_map(fn (ShouldBeStored $event) => get_class($event), $this->aggregateRoot->getRecordedEvents());

        $unexpectedEventClasses = Arr::wrap($unexpectedEventClasses);

        foreach ($unexpectedEventClasses as $nonExceptedEventClass) {
            Assert::assertNotContains($nonExceptedEventClass, $actualEventClasses, "Did not expect to record {$nonExceptedEventClass}, but it was recorded.");
        }

        return $this;
    }

    public function assertEventRecorded(ShouldBeStored $expectedEvent): self
    {
        $recordedEvents = $this->getRecordedEventsWithoutUuid();

        Assert::assertContainsEquals($expectedEvent, $recordedEvents);

        return $this;
    }

    public function assertNothingApplied(): self
    {
        Assert::assertCount(0, $this->aggregateRoot->getAppliedEvents());

        return $this;
    }

    /**
     * @param \Spatie\EventSourcing\StoredEvents\ShouldBeStored|\Spatie\EventSourcing\StoredEvents\ShouldBeStored[] $expectedEvents
     *
     * @return $this
     */
    public function assertApplied($expectedEvents): self
    {
        $expectedEvents = Arr::wrap($expectedEvents);

        $appliedEvents = array_map(function (ShouldBeStored $event) {
            $metaData = $event->metaData();

            unset($metaData[MetaData::AGGREGATE_ROOT_UUID]);

            return $event->setMetaData($metaData);
        }, $this->aggregateRoot->getAppliedEvents());

        Assert::assertEquals($expectedEvents, $appliedEvents);

        return $this;
    }

    public function assertNotApplied($unexpectedEventClasses): void
    {
        $actualEventClasses = array_map(fn (ShouldBeStored $event) => get_class($event), $this->aggregateRoot->getAppliedEvents());

        $unexpectedEventClasses = Arr::wrap($unexpectedEventClasses);

        foreach ($unexpectedEventClasses as $nonExceptedEventClass) {
            Assert::assertNotContains($nonExceptedEventClass, $actualEventClasses, "Did not expect to apply {$nonExceptedEventClass}, but it was applied.");
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

    private function getRecordedEventsWithoutUuid(): array
    {
        return array_map(static function (ShouldBeStored $event) {
            $metaData = $event->metaData();

            unset($metaData[MetaData::AGGREGATE_ROOT_UUID]);

            return $event->setMetaData($metaData);
        }, $this->aggregateRoot->getRecordedEvents());
    }
}
