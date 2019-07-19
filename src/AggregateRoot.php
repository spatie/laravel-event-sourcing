<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\EventProjector\Models\StoredEvent;

abstract class AggregateRoot
{
    /** @var string */
    private $aggregateUuid;

    /** @var array */
    private $recordedEvents = [];

    public static function retrieve(string $uuid): AggregateRoot
    {
        $aggregateRoot = (new static());

        $aggregateRoot->aggregateUuid = $uuid;

        return $aggregateRoot->reconstituteFromEvents();
    }

    public function recordThat(ShouldBeStored $domainEvent): AggregateRoot
    {
        $this->recordedEvents[] = $domainEvent;

        $this->apply($domainEvent);

        return $this;
    }

    public function persist(): AggregateRoot
    {
        call_user_func(
            [$this->getStoredEventModel(), 'storeMany'],
            $this->getAndClearRecordedEvents(),
            $this->aggregateUuid
        );

        return $this;
    }

    protected function getStoredEventModel(): string
    {
        return $this->storedEventModel ?? config('event-projector.stored_event_model');
    }

    public function getRecordedEvents(): array
    {
        return $this->recordedEvents;
    }

    private function getAndClearRecordedEvents(): array
    {
        $recordedEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $recordedEvents;
    }

    private function reconstituteFromEvents(): AggregateRoot
    {
        $this->getStoredEventModel()::uuid($this->aggregateUuid)->each(function (StoredEvent $storedEvent) {
            $this->apply($storedEvent->event);
        });

        return $this;
    }

    private function apply(ShouldBeStored $event): void
    {
        $classBaseName = class_basename($event);

        $camelCasedBaseName = ucfirst(Str::camel($classBaseName));

        $applyingMethodName = "apply{$camelCasedBaseName}";

        if (method_exists($this, $applyingMethodName)) {
            $this->$applyingMethodName($event);
        }
    }

    /**
     * @param \Spatie\EventProjector\ShouldBeStored|\Spatie\EventProjector\ShouldBeStored[] $events
     *
     * @return $this
     */
    public static function fake($events = []): FakeAggregateRoot
    {
        $events = Arr::wrap($events);

        return (new FakeAggregateRoot(app(static::class)))->given($events);
    }
}
