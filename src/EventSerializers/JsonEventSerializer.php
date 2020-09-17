<?php

namespace Spatie\EventSourcing\EventSerializers;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Spatie\EventSourcing\Support\CarbonNormalizer;
use Spatie\EventSourcing\Support\ModelIdentifierNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class JsonEventSerializer implements EventSerializer
{
	private const DEFAULT_NORMALIZERS = [
		CarbonNormalizer::class,
		ModelIdentifierNormalizer::class,
		DateTimeNormalizer::class,
		ObjectNormalizer::class,
	];
	
    private SymfonySerializer $serializer;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        
        $normalizerClassNames = config('event-sourcing.event_normalizers', static::DEFAULT_NORMALIZERS);
        $normalizers = array_map(fn($className) => app($className), $normalizerClassNames);

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

    public function deserialize(string $eventClass, string $json, string $metadata = null): ShouldBeStored
    {
        return $this->serializer->deserialize($json, $eventClass, 'json');
    }
}
