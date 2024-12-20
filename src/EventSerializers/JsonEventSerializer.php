<?php

namespace Spatie\EventSourcing\EventSerializers;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class JsonEventSerializer implements EventSerializer
{
    private SymfonySerializer $serializer;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = array_map(
            fn ($className) => new $className(),
            config('event-sourcing.event_normalizers')
        );

        $this->serializer = new SymfonySerializer($normalizers, $encoders);
    }

    public function serialize(ShouldBeStored $event): string
    {
        /*
         * We call __sleep so that the event can prepare for serialization.
         */
        if (method_exists($event, '__sleep')) {
            $event->__sleep();
        }

        return $this->serializer->serialize($event, 'json');
    }

    public function deserialize(
        string $eventClass,
        string $json,
        int $version,
        ?string $metadata = null
    ): ShouldBeStored {
        return $this->serializer->deserialize($json, $eventClass, 'json');
    }
}
