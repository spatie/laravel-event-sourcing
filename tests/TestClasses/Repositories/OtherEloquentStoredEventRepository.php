<?php

namespace Spatie\EventProjector\Tests\TestClasses\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\EventProjector\ShouldBeStored;
use Spatie\EventProjector\StoredEventRepository;
use Spatie\EventProjector\Models\StoredEventData;
use Spatie\EventProjector\EventSerializers\EventSerializer;
use Spatie\EventProjector\Tests\TestClasses\Models\OtherStoredEvent;

class OtherEloquentStoredEventRepository implements StoredEventRepository
{
    public static function retrieveAll(string $uuid = null, int $startingFrom = null): Collection
    {
        $query = OtherStoredEvent::query();

        if ($uuid) {
            $query->uuid($uuid);
        }

        if ($startingFrom) {
            $query->startingFrom($startingFrom);
        }

        return $query->get()->map(function (OtherStoredEvent $storedEvent) {
            return $storedEvent->toStoredEventData();
        });
    }

    public static function persist(ShouldBeStored $event, string $uuid = null): StoredEventData
    {
        /** @var OtherStoredEvent $storedEvent */
        $storedEvent = new OtherStoredEvent();

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
        $storedEvent = OtherStoredEvent::find($storedEventData->id);

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
