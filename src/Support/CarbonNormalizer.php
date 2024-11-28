<?php

namespace Spatie\EventSourcing\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CarbonNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        if (! $object instanceof CarbonInterface) {
            throw new InvalidArgumentException('Cannot serialize an object that is not a CarbonInterface in CarbonNormalizer.');
        }

        return $object->toRfc3339String();
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CarbonInterface;
    }

    /**
     * @inheritDoc
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): CarbonInterface
    {
        return Date::parse($data);
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_a($type, CarbonInterface::class, true);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [CarbonInterface::class => false];
    }
}
