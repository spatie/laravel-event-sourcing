<?php

namespace Spatie\EventSourcing\AggregateRoots;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionProperty;
use Spatie\EventSourcing\Exceptions\CouldNotPersistAggregate;
use Spatie\EventSourcing\Snapshots\Snapshot;
use Spatie\EventSourcing\Snapshots\SnapshotRepository;
use Spatie\EventSourcing\StoredEvents\Repositories\StoredEventRepository;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

abstract class AggregateRoot
{
    private string $uuid = '';

    private array $recordedEvents = [];

    private array $appliedEvents = [];

    protected int $aggregateVersion = 0;

    protected int $aggregateVersionAfterReconstitution = 0;

    protected static bool $allowConcurrency = false;

    /**
     * @param string $uuid
     *
     * @return static
     */
    public static function retrieve(string $uuid)
    {
        $aggregateRoot = app(static::class);

        $aggregateRoot->uuid = $uuid;

        return $aggregateRoot->reconstituteFromEvents();
    }

    public function loadUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this->reconstituteFromEvents();
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param \Spatie\EventSourcing\StoredEvents\ShouldBeStored $domainEvent
     *
     * @return static
     */
    public function recordThat(ShouldBeStored $domainEvent)
    {
        $domainEvent->setAggregateRootUuid($this->uuid);

        $this->recordedEvents[] = $domainEvent;

        $this->apply($domainEvent);

        return $this;
    }

    /**
     * @return static
     */
    public function persist()
    {
        $storedEvents = $this->persistWithoutApplyingToEventHandlers();

        $storedEvents->each(fn (StoredEvent $storedEvent) => $storedEvent->handleForAggregateRoot());

        $this->aggregateVersionAfterReconstitution = $this->aggregateVersion;

        return $this;
    }

    protected function persistWithoutApplyingToEventHandlers(): LazyCollection
    {
        $this->ensureNoOtherEventsHaveBeenPersisted();

        $storedEvents = $this
            ->getStoredEventRepository()
            ->persistMany(
                $this->getAndClearRecordedEvents(),
                $this->uuid(),
                $this->aggregateVersion,
            );

        return $storedEvents;
    }

    public function snapshot(): Snapshot
    {
        return $this->getSnapshotRepository()->persist(new Snapshot(
            $this->uuid,
            $this->aggregateVersion,
            $this->getState(),
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

    public function getAppliedEvents(): array
    {
        return $this->appliedEvents;
    }

    protected function getState(): array
    {
        $class = new ReflectionClass($this);

        return collect($class->getProperties(ReflectionProperty::IS_PUBLIC))
            ->reject(fn (ReflectionProperty $reflectionProperty) => $reflectionProperty->isStatic())
            ->mapWithKeys(function (ReflectionProperty $property) {
                return [$property->getName() => $this->{$property->getName()}];
            })->toArray();
    }

    protected function useState(array $state): void
    {
        foreach ($state as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function getAndClearRecordedEvents(): array
    {
        $recordedEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $recordedEvents;
    }

    protected function reconstituteFromEvents(): self
    {
        $storedEventRepository = $this->getStoredEventRepository();
        $snapshot = $this->getSnapshotRepository()->retrieve($this->uuid);

        if ($snapshot) {
            $this->aggregateVersion = $snapshot->aggregateVersion;
            $this->useState($snapshot->state);
        }

        $storedEventRepository->retrieveAllAfterVersion($this->aggregateVersion, $this->uuid)
            ->each(function (StoredEvent $storedEvent) {
                $this->apply($storedEvent->event);
            });

        $this->aggregateVersionAfterReconstitution = $this->aggregateVersion;

        return $this;
    }

    protected function ensureNoOtherEventsHaveBeenPersisted(): void
    {
        if (static::$allowConcurrency) {
            return;
        }

        $latestPersistedVersionId = $this->getStoredEventRepository()->getLatestAggregateVersion($this->uuid);

        if ($this->aggregateVersionAfterReconstitution !== $latestPersistedVersionId) {
            throw CouldNotPersistAggregate::unexpectedVersionAlreadyPersisted(
                $this,
                $this->uuid,
                $this->aggregateVersionAfterReconstitution,
                $latestPersistedVersionId,
            );
        }
    }

    private function apply(ShouldBeStored $event): void
    {
        $classBaseName = class_basename($event);

        $camelCasedBaseName = ucfirst(Str::camel($classBaseName));

        $applyingMethodName = "apply{$camelCasedBaseName}";

        $reflectionClass = new ReflectionClass($this);

        $applyMethodExists = $reflectionClass->hasMethod($applyingMethodName);
        $applyMethodIsPublic = $applyMethodExists && $reflectionClass->getMethod($applyingMethodName)->isPublic();

        if ($applyMethodExists && $applyMethodIsPublic) {
            try {
                app()->call([$this, $applyingMethodName], ['event' => $event]);
            } catch (BindingResolutionException $exception) {
                $this->$applyingMethodName($event);
            }
        } elseif ($applyMethodExists) {
            $this->$applyingMethodName($event);
        }

        $this->appliedEvents[] = $event;

        $this->aggregateVersion++;
    }

    public static function fake(string $uuid = null): FakeAggregateRoot
    {
        $uuid ??= (string)Str::uuid();

        $aggregateRoot = static::retrieve($uuid);

        return (new FakeAggregateRoot($aggregateRoot));
    }

    public static function persistInTransaction(AggregateRoot ...$aggregateRoots): void
    {
        $storedEvents = DB::transaction(function () use ($aggregateRoots) {
            return collect($aggregateRoots)
                ->flatMap(function (AggregateRoot $aggregateRoot) {
                    return $aggregateRoot->persistWithoutApplyingToEventHandlers()->all();
                });
        });

        /** @var \Spatie\EventSourcing\Projectionist $projectionist */
        $projectionist = app('event-sourcing');

        $projectionist->handleStoredEvents($storedEvents);
    }
}
