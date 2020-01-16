<?php

namespace Spatie\EventSourcing;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionProperty;

abstract class AggregateRoot
{
    private string $aggregateUuid;

    private array $recordedEvents = [];

    protected int $aggregateVersion = 0;

    /**
     * @param  string  $uuid
     * @return static
     */
    public static function retrieve(string $uuid): AggregateRoot
    {
        $aggregateRoot = (new static());

        $aggregateRoot->aggregateUuid = $uuid;

        return $aggregateRoot->reconstituteFromEvents();
    }

    /**
     * @param  ShouldBeStored  $domainEvent
     * @return static
     */
    public function recordThat(ShouldBeStored $domainEvent): AggregateRoot
    {
        $this->recordedEvents[] = $domainEvent;

        $this->apply($domainEvent);

        return $this;
    }

    /**
     * @return static
     */
    public function persist(): AggregateRoot
    {
        $storedEvents = call_user_func(
            [$this->getStoredEventRepository(), 'persistMany'],
            $this->getAndClearRecordedEvents(),
            $this->aggregateUuid ?? '',
            $this->aggregateVersion,
        );

        $storedEvents->each(function (StoredEvent $storedEvent) {
            $storedEvent->handle();
        });

        return $this;
    }

    public function snapshot(): Snapshot
    {
        return $this->getSnapshotRepository()->persist(new Snapshot(
            $this->aggregateUuid,
            $this->aggregateVersion,
            $this->state(),
        ));
    }

    protected function getSnapshotRepository(): SnapshotRepository
    {
        return app($this->snapshotRepository ?? config('event-sourcing.snapshot_repository'));
    }

    protected function getStoredEventRepository(): StoredEventRepository
    {
        return app($this->storedEventRepository ?? config('event-sourcing.stored_event_repository'));
    }

    public function getRecordedEvents(): array
    {
        return $this->recordedEvents;
    }

    private function state(): array
    {
        $class = new ReflectionClass($this);

        return collect($class->getProperties(ReflectionProperty::IS_PUBLIC))
            ->mapWithKeys(function (ReflectionProperty $property) {
                return [$property->getName() => $this->{$property->getName()}];
            })->toArray();
    }

    private function getAndClearRecordedEvents(): array
    {
        $recordedEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $recordedEvents;
    }

    private function reconstituteFromEvents(): AggregateRoot
    {
        $storedEventRepository = $this->getStoredEventRepository();

        if (! $snapshot = $this->getSnapshotRepository()->retrieve($this->aggregateUuid)) {
            $storedEventRepository->retrieveAll($this->aggregateUuid)
                ->each(function (StoredEvent $storedEvent) {
                    $this->apply($storedEvent->event);
                });

            return $this;
        }

        $this->aggregateVersion = $snapshot->aggregateVersion;
        foreach ($snapshot->state as $key => $value) {
            $this->$key = $value;
        }

        $storedEventRepository->retrieveAllAfterVersion($this->aggregateVersion, $this->aggregateUuid)
            ->each(function (StoredEvent $storedEvent) {
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

        ++$this->aggregateVersion;
    }

    /**
     * @param \Spatie\EventSourcing\ShouldBeStored|\Spatie\EventSourcing\ShouldBeStored[] $events
     *
     * @return $this
     */
    public static function fake($events = []): FakeAggregateRoot
    {
        $events = Arr::wrap($events);

        return (new FakeAggregateRoot(app(static::class)))->given($events);
    }
}
