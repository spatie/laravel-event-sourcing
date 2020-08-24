<?php

namespace Spatie\EventSourcing\EventSerializers;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\Support\CarbonNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class JsonEventSerializer implements EventSerializer
{
    private SymfonySerializer $serializer;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new CarbonNormalizer(), new DateTimeNormalizer(), new ObjectNormalizer()];

        $this->serializer = new SymfonySerializer($normalizers, $encoders);
    }

    public function serialize(ShouldBeStored $event): string
    {
        /*
         * We call __sleep so `Illuminate\Queue\SerializesModels` will
         * prepare all models in the event for serialization.
         */
        if (method_exists($event, '__sleep')) {
            $event->__sleep();
        }

        return $this->serializer->serialize($event, 'json');
    }

    public function deserialize(string $eventClass, string $json, string $metadata = null): ShouldBeStored
    {
        $restoredEvent = $this->serializer->deserialize($json, $eventClass, 'json');

        /*
         *  We call manually serialize and unserialize to trigger
         * `Illuminate\Queue\SerializesModels` model restoring capabilities.
         */
        return unserialize(serialize($restoredEvent));
    }
}
