<?php

namespace Spatie\EventProjector\Tests\TestClasses\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\EventProjector\Models\StoredEventData;
use Spatie\EventProjector\ShouldBeStored;
use Spatie\EventProjector\StoredEventRepository;

class JsonStoredEventRepository implements StoredEventRepository
{

    public function retrieveAll(string $uuid = null, int $startingFrom = null): Collection
    {
        /** @var Collection $events */
        $events = Cache::get('stored-events');

        if ($uuid) {
            $events = $events
        }

        if ($startingFrom) {
            return $events->filter(function ($event, $index) use ($startingFrom) {
                return $index > $startingFrom;
            });
        }

        return $events;
    }

    public function persist(ShouldBeStored $event, string $uuid = null): StoredEventData
    {
        $events = Cache::get('stored-events');

    }

    public function update(StoredEventData $storedEventData): StoredEventData
    {
        // TODO: Implement update() method.
    }
}
