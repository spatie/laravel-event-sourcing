<?php

namespace Spatie\EventProjector\Tests\TestClasses\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\EventProjector\ShouldBeStored;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\EloquentStoredEventRepository;
use Spatie\EventProjector\EventSerializers\EventSerializer;
use Spatie\EventProjector\Tests\TestClasses\Models\OtherEloquentStoredEvent;

class OtherEloquentStoredEventRepository extends EloquentStoredEventRepository
{
    public static function retrieveAll(string $uuid = null, int $startingFrom = null): Collection
    {
        $query = OtherEloquentStoredEvent::query();

        if ($uuid) {
            $query->uuid($uuid);
        }

        if ($startingFrom) {
            $query->startingFrom($startingFrom);
        }

        return $query->get()->map(function (OtherEloquentStoredEvent $storedEvent) {
            return $storedEvent->toStoredEvent();
        });
    }

    public static function persist(ShouldBeStored $event, string $uuid = null): StoredEvent
    {
        /** @var OtherEloquentStoredEvent $storedEvent */
        $storedEvent = new OtherEloquentStoredEvent();

        $storedEvent->setRawAttributes([
            'event_properties' => app(EventSerializer::class)->serialize(clone $event),
            'aggregate_uuid' => $uuid,
            'event_class' => self::getEventClass(get_class($event)),
            'meta_data' => json_encode([]),
            'created_at' => Carbon::now(),
        ]);

        $storedEvent->save();

        return $storedEvent->toStoredEvent();
    }

    public static function persistMany(array $events, string $uuid = null): Collection
    {
        $storedEvents = [];

        foreach ($events as $event) {
            $storedEvents[] = self::persist($event, $uuid);
        }

        return collect($storedEvents);
    }

    public static function update(StoredEvent $storedEvent): StoredEvent
    {
        /** @var OtherEloquentStoredEvent $storedEvent */
        $storedEvent = OtherEloquentStoredEvent::find($storedEvent->id);

        $storedEvent->update($storedEvent->toArray());

        return $storedEvent->toStoredEvent();
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
