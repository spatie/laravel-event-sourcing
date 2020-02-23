<?php

namespace Spatie\EventSourcing\EventSerializers;

use Spatie\EventSourcing\ShouldBeStored;
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
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer()];

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

        $json = $this->serializer->serialize($event, 'json');

        return $json;
    }

    public function deserialize(string $eventClass, string $json): ShouldBeStored
    {
        $restoredEvent = $this->serializer->deserialize($json, $eventClass, 'json');

        /*
         *  We call manually serialize and unserialize to trigger
         * `Illuminate\Queue\SerializesModels` model restoring capabilities.
         */
        return unserialize(serialize($restoredEvent));
    }
}
