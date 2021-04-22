<?php

namespace Spatie\EventSourcing\AggregateRoots;

use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use Spatie\EventSourcing\Exceptions\CouldNotPersistAggregate;
use Spatie\EventSourcing\Exceptions\UnhandledCommand;
use Spatie\EventSourcing\Handlers;
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

    /** @var \Illuminate\Support\Collection|\Spatie\EventSourcing\AggregateRoots\AggregatePartial[] */
    protected Collection $entities;

    private bool $handleEvents = true;

    /**
     * @param string $uuid
     *
     * Psalm needs this doc block to properly know the return type.
     *
     * @return static
     */
    public static function retrieve(string $uuid): static
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

    public function handleCommand(object $command): self
    {
        if ($handler = Handlers::find($command, $this)[0] ?? null) {
            $this->{$handler}($command);

            return $this;
        }

        foreach ($this->resolvePartials() as $partial) {
            $handler = Handlers::find($command, $partial)[0] ?? null;

            if (! $handler) {
                continue;
            }

            $partial->{$handler}($command);

            return $this;
        }

        throw new UnhandledCommand($command::class);
    }

    public function recordThat(ShouldBeStored $domainEvent): self
    {
        $domainEvent
            ->setAggregateRootUuid($this->uuid)
            ->setCreatedAt(CarbonImmutable::now());

        $this->recordedEvents[] = $domainEvent;

        $this->apply($domainEvent);

        $domainEvent->setAggregateRootVersion($this->aggregateVersion);

        return $this;
    }

    public function persist(): self
    {
        $storedEvents = $this->persistWithoutApplyingToEventHandlers();

        if ($this->handleEvents) {
            $storedEvents->each(fn (StoredEvent $storedEvent) => $storedEvent->handleForAggregateRoot());
        }

        $this->aggregateVersionAfterReconstitution = $this->aggregateVersion;

        return $this;
    }

    protected function persistWithoutApplyingToEventHandlers(): LazyCollection
    {
        $this->ensureNoOtherEventsHaveBeenPersisted();

        try {
            $storedEvents = $this
                ->getStoredEventRepository()
                ->persistMany(
                    $this->getAndClearRecordedEvents(),
                    $this->uuid(),
                );
        } catch (QueryException $exception) {
            if (! str_contains($exception->getMessage(), 'Duplicate')) {
                throw $exception;
            }

            throw CouldNotPersistAggregate::invalidVersion(
                $this,
                $this->aggregateVersion
            );
        }

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

        if ($snapshot = $this->getSnapshotRepository()->retrieve($this->uuid)) {
            $this->aggregateVersion = $snapshot->aggregateVersion;
            $this->useState($snapshot->state);

            $events = $storedEventRepository->retrieveAllAfterVersion($this->aggregateVersion, $this->uuid);
        } else {
            $events = $storedEventRepository->retrieveAll($this->uuid);
        }

        $events->each(function (StoredEvent $storedEvent) {
            $this->apply($storedEvent->event);
        });

        $this->aggregateVersionAfterReconstitution = $this->aggregateVersion;

        return $this;
    }

    protected function ensureNoOtherEventsHaveBeenPersisted(): void
    {
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

    protected function apply(ShouldBeStored $event): void
    {
        foreach ($this->resolvePartials() as $partial) {
            $partial->apply($event);
        }

        $handlers = Handlers::find($event, $this);

        $handlers->each(fn (string $handler) => $this->{$handler}($event));

        $this->appliedEvents[] = $event;

        $this->aggregateVersion++;
    }

    /**
     * @return \Illuminate\Support\Collection|\Spatie\EventSourcing\AggregateRoots\AggregatePartial[]
     */
    protected function resolvePartials(): Collection
    {
        if (! isset($this->entities)) {
            $this->entities = collect((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PUBLIC))
                ->mapWithKeys(fn (ReflectionProperty $property) => [$property->getName() => $property->getType()])
                ->filter(fn (?ReflectionType $type) => $type instanceof ReflectionNamedType)
                ->filter(fn (ReflectionNamedType $type) => is_subclass_of($type->getName(), AggregatePartial::class))
                ->map(fn (ReflectionNamedType $type, string $propertyName) => $this->{$propertyName});
        }

        return $this->entities;
    }

    public static function fake(string $uuid = null): FakeAggregateRoot
    {
        $uuid ??= (string) Str::uuid();

        $aggregateRoot = static::retrieve($uuid)->disableEventHandling();

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

    private function disableEventHandling(): self
    {
        $this->handleEvents = false;

        return $this;
    }
}
