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
    public function normalize($object, string $format = null, array $context = [])
    {
        if (! $object instanceof CarbonInterface) {
            throw new InvalidArgumentException('Cannot serialize an object that is not a CarbonInterface in CarbonNormalizer.');
        }

        return $object->toRfc3339String();
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof CarbonInterface;
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, $class, string $format = null, array $context = [])
    {
        return Date::parse($data);
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization($data, $type, string $format = null)
    {
        return is_a($type, CarbonInterface::class, true);
    }
}
