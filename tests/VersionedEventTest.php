<?php

namespace Spatie\EventSourcing\Tests;

use Spatie\EventSourcing\Attributes\EventSerializer;
use Spatie\EventSourcing\Attributes\EventVersion;
use Spatie\EventSourcing\EventSerializers\JsonEventSerializer;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class VersionedEventTest extends TestCase
{
    /** @test */
    public function the_event_version_is_saved_in_the_database()
    {
        $event = new VersionedEvent('uuid-1');

        event($event);

        /** @var \Spatie\EventSourcing\StoredEvents\StoredEvent $storedEvent */
        $storedEvent = EloquentStoredEvent::find($event->storedEventId())->toStoredEvent();

        $this->assertEquals(2, $storedEvent->event_version);
    }

    /** @test */
    public function a_versioned_event_can_be_restored()
    {
        /** @var \Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent $storedEventV1 */
        $storedEventV1 = EloquentStoredEvent::create([
            "id" => 1,
            "aggregate_uuid" => null,
            "aggregate_version" => null,
            "event_version" => 1,
            "event_class" => "Spatie\\EventSourcing\\Tests\\VersionedEvent",
            "event_properties" => ['name' => 'event-1'],
            "meta_data" => [],
            "created_at" => now(),
        ]);

        /** @var \Spatie\EventSourcing\Tests\VersionedEvent $event */
        $event = $storedEventV1->toStoredEvent()->event;

        $this->assertEquals('event-1', $event->uuid);
    }
}

#[
    EventVersion(2),
    EventSerializer(VersionedEventSerializer::class),
]
class VersionedEvent extends ShouldBeStored
{
    public function __construct(
        public string $uuid
    ) {
    }
}

class VersionedEventSerializer extends JsonEventSerializer
{
    public function deserialize(
        string $eventClass,
        string $json,
        int $version,
        string $metadata = null
    ): VersionedEvent {
        $data = json_decode($json, true);

        if ($version === 1) {
            $data['uuid'] = $data['name'] ?? '';
            unset($data['name']);
        }

        return new VersionedEvent(...$data);
    }
}
