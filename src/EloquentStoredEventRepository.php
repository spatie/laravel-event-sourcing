<?php

namespace Spatie\EventProjector;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Models\EloquentStoredEvent;
use Spatie\EventProjector\EventSerializers\EventSerializer;

class EloquentStoredEventRepository implements StoredEventRepository
{
    public static function retrieveAll(string $uuid = null, int $startingFrom = null): Collection
    {
        $query = EloquentStoredEvent::query();

        if ($uuid) {
            $query->uuid($uuid);
        }

        if ($startingFrom) {
            $query->startingFrom($startingFrom);
        }

        return $query->get()->map(function (EloquentStoredEvent $storedEvent) {
            return $storedEvent->toStoredEventData();
        });
    }

    public static function persist(ShouldBeStored $event, string $uuid = null): StoredEvent
    {
        $storedEvent = new EloquentStoredEvent();

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

    public static function update(StoredEvent $storedEventData): StoredEvent
    {
        $storedEvent = EloquentStoredEvent::find($storedEventData->id);

        $storedEvent->update($storedEventData->toArray());

        return $storedEvent->toStoredEventData();
    }

    private static function getEventClass(string $class): string
    {
        $map = config('event-projector.event_class_map', []);

        if (! empty($map) && in_array($class, $map)) {
            return array_search($class, $map, true);
        }

        return $class;
    }
}
