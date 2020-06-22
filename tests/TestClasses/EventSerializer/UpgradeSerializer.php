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

class UpgradeSerializer extends JsonEventSerializer
{
    public function deserialize(string $eventClass, string $json, string $metadata = null): ShouldBeStored
    {
        $event = parent::deserialize($eventClass, $json, $metadata);

        $metadata = json_decode($metadata, true);
        if ($metadata['version'] < 2) {
            $event->value = $event->value->setTimezone(new DateTimeZone('UTC'));
        }

        return $event;
    }
}
