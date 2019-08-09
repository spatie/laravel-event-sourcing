<?php

namespace Spatie\EventProjector;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\EventProjector\EventSerializers\EventSerializer;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Models\StoredEventData;

class EloquentStoredEventRepository implements StoredEventRepository
{
    public static function retrieveAll(string $uuid = null, int $startingFrom = null): Collection
    {
        $query = StoredEvent::query();

        if ($uuid) {
            $query->uuid($uuid);
        }

        if ($startingFrom) {
            $query->startingFrom($startingFrom);
        }

        return $query->get()->map(function (StoredEvent $storedEvent) {
            return $storedEvent->toStoredEventData();
        });
    }

    public static function persist(ShouldBeStored $event, string $uuid = null): StoredEventData
    {
        /** @var StoredEvent $storedEvent */
        $storedEvent = new StoredEvent();

        $storedEvent->setRawAttributes([
            'event_properties' => app(EventSerializer::class)->serialize(clone $event),
            'aggregate_uuid' => $uuid,
            'event_class' => self::getEventClass(get_class($event)),
            'meta_data' => json_encode([]),
            'created_at' => Carbon::now(),
        ]);

        $storedEvent->save();

        return $storedEvent->toStoredEventData();
    }

    public static function persistMany(array $events, string $uuid = null): Collection
    {
        $storedEvents = [];

        foreach ($events as $event) {
            $storedEvents[] = self::persist($event, $uuid);
        }

        return collect($storedEvents);
    }

    public static function update(StoredEventData $storedEventData): StoredEventData
    {
        $storedEvent = StoredEvent::find($storedEventData->id);

        $storedEvent->update($storedEventData->toArray());

        return $storedEvent->toStoredEventData();
    }

    protected static function getEventClass(string $class): string
    {
        $map = config('event-projector.event_class_map', []);

        if (! empty($map) && in_array($class, $map)) {
            return array_search($class, $map, true);
        }

        return $class;
    }
}
