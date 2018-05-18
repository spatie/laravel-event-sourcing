<?php

namespace Spatie\EventProjector;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EventSerializer
{
    /** @var \Symfony\Component\Serializer\Serializer */
    protected $serializer;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function serialize(ShouldBeStored $event): string
    {
        /*
         * We call __sleep so `Illuminate\Queue\SerializesModels` will
         * prepare all models in the event for serialization.
         */
        $event->__sleep();

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
