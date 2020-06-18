<?php

namespace Spatie\EventSourcing\Tests\TestClasses\EventSerializer;

use DateTimeZone;
use Spatie\EventSourcing\EventSerializers\EventSerializer;
use Spatie\EventSourcing\EventSerializers\JsonEventSerializer;
use Spatie\EventSourcing\ShouldBeStored;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class UpgradeSerializer implements EventSerializer
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

    public function deserialize(string $eventClass, string $json, string $metadata = null): ShouldBeStored
    {
        $restoredEvent = $this->serializer->deserialize($json, $eventClass, 'json');

        $metadata = json_decode($metadata, true);
        if ($metadata['version'] < 2) {
            $restoredEvent->value = $restoredEvent->value->setTimezone(new DateTimeZone('UTC'));
        }

        return unserialize(serialize($restoredEvent));
    }
}
