<?php

namespace Spatie\EventSourcing\Support;

use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ModelIdentifierNormalizer implements NormalizerInterface, DenormalizerInterface
{
    use SerializesAndRestoresModelIdentifiers;

    /**
     * @inheritdoc
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (! $this->supportsNormalization($object)) {
            throw new InvalidArgumentException('Cannot serialize an object that is not a QueueableEntity or QueueableCollection in ModelIdentifierNormalizer.');
        }

        return (array) $this->getSerializedPropertyValue($object);
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return ($data instanceof QueueableEntity || $data instanceof QueueableCollection);
    }

    /**
     * @inheritdoc
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Collection|Model
    {
        $identifier = $data instanceof ModelIdentifier
            ? $data
            : new ModelIdentifier($data['class'], $data['id'], $data['relations'], $data['connection']);

        return $this->getRestoredPropertyValue($identifier);
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $this->normalizedDataIsModelIdentifier($data)
            && $this->isNormalizedToModelIdentifier($type);
    }

    protected function normalizedDataIsModelIdentifier($data): bool
    {
        return $data instanceof ModelIdentifier
            || isset($data['class'], $data['id'], $data['relations'], $data['connection']);
    }

    protected function isNormalizedToModelIdentifier($class): bool
    {
        return is_a($class, QueueableEntity::class, true)
            || is_a($class, QueueableCollection::class, true);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [QueueableEntity::class => false, QueueableCollection::class => false];
    }
}
