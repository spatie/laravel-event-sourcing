<?php

namespace Spatie\EventSourcing\StoredEvents\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\LazyCollection;
use Spatie\EventSourcing\AggregateRoots\Exceptions\InvalidEloquentStoredEventModel;
use Spatie\EventSourcing\Enums\MetaData;
use Spatie\EventSourcing\EventSerializers\EventSerializer;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEventQueryBuilder;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

class EloquentStoredEventRepository implements StoredEventRepository
{
    protected string $storedEventModel;

    public function __construct()
    {
        $this->storedEventModel = (string) config('event-sourcing.stored_event_model', EloquentStoredEvent::class);

        if (! new $this->storedEventModel() instanceof EloquentStoredEvent) {
            throw new InvalidEloquentStoredEventModel("The class {$this->storedEventModel} must extend EloquentStoredEvent");
        }
    }

    public function find(int $id): StoredEvent
    {
        $eloquentStoredEvent = $this->getQuery()->where('id', $id)->first();

        return $eloquentStoredEvent->toStoredEvent();
    }

    public function retrieveAll(string $uuid = null): LazyCollection
    {
        $query = $this->getQuery();

        if ($uuid) {
            $query->whereAggregateRoot($uuid);
        }

        return $query->orderBy('id')->cursor()->map(fn (EloquentStoredEvent $storedEvent) => $storedEvent->toStoredEvent());
    }

    public function retrieveAllStartingFrom(int $startingFrom, string $uuid = null): LazyCollection
    {
        $query = $this->prepareEventModelQuery($startingFrom, $uuid);

        /** @var LazyCollection $lazyCollection */
        $lazyCollection = $query
            ->orderBy('id')
            ->cursor();

        return $lazyCollection->map(fn (EloquentStoredEvent $storedEvent) => $storedEvent->toStoredEvent());
    }

    public function countAllStartingFrom(int $startingFrom, string $uuid = null): int
    {
        return $this->prepareEventModelQuery($startingFrom, $uuid)->count('id');
    }

    public function retrieveAllAfterVersion(int $aggregateVersion, string $aggregateUuid): LazyCollection
    {
        $query = $this->getQuery()
            ->whereAggregateRoot($aggregateUuid)
            ->afterVersion($aggregateVersion);

        return $query
            ->orderBy('id')
            ->cursor()
            ->map(fn (EloquentStoredEvent $storedEvent) => $storedEvent->toStoredEvent());
    }

    public function persist(ShouldBeStored $event, string $uuid = null): StoredEvent
    {
        /** @var EloquentStoredEvent $eloquentStoredEvent */
        $eloquentStoredEvent = new $this->storedEventModel();

        $eloquentStoredEvent->setOriginalEvent($event);

        $createdAt = Carbon::now();

        $eloquentStoredEvent->setRawAttributes([
            'event_properties' => app(EventSerializer::class)->serialize(clone $event),
            'aggregate_uuid' => $uuid,
            'aggregate_version' => $event->aggregateRootVersion(),
            'event_version' => $event->eventVersion(),
            'event_class' => $this->getEventClass(get_class($event)),
            'meta_data' => json_encode($event->metaData() + [
                MetaData::CREATED_AT => $createdAt->toDateTimeString(),
            ]),
            'created_at' => $createdAt,
        ]);

        $eloquentStoredEvent->save();

        $eloquentStoredEvent->meta_data->set(MetaData::STORED_EVENT_ID, $eloquentStoredEvent->id);

        $eloquentStoredEvent->save();

        $eloquentStoredEvent->event->setStoredEventId($eloquentStoredEvent->id);

        return $eloquentStoredEvent->toStoredEvent();
    }

    public function persistMany(array $events, string $uuid = null): LazyCollection
    {
        $storedEvents = [];

        /** @var \Spatie\EventSourcing\StoredEvents\ShouldBeStored $event */
        foreach ($events as $event) {
            $storedEvents[] = $this->persist($event, $uuid);
        }

        return new LazyCollection($storedEvents);
    }

    public function update(StoredEvent $storedEvent): StoredEvent
    {
        /** @var EloquentStoredEvent $eloquentStoredEvent */
        $eloquentStoredEvent = $this->getQuery()->find($storedEvent->id);

        $eloquentStoredEvent->update($storedEvent->toArray());

        return $eloquentStoredEvent->toStoredEvent();
    }

    private function getEventClass(string $class): string
    {
        $map = config('event-sourcing.event_class_map', []);

        if (! empty($map) && in_array($class, $map)) {
            return array_search($class, $map, true);
        }

        return $class;
    }

    public function getLatestAggregateVersion(string $aggregateUuid): int
    {
        return $this->getQuery()
                ->whereAggregateRoot($aggregateUuid)
                ->max('aggregate_version') ?? 0;
    }

    private function prepareEventModelQuery(int $startingFrom, string $uuid = null): Builder
    {
        $query = $this->getQuery()->startingFrom($startingFrom);

        if ($uuid) {
            $query->whereAggregateRoot($uuid);
        }

        return $query;
    }

    private function getQuery(): EloquentStoredEventQueryBuilder
    {
        return $this->storedEventModel::query();
    }
}
