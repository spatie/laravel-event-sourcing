<?php

namespace Spatie\EventSourcing\Support;

use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Contracts\Queue\QueueableEntity;
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
    public function normalize($object, string $format = null, array $context = [])
    {
        if (! $this->supportsNormalization($object)) {
            throw new InvalidArgumentException('Cannot serialize an object that is not a QueueableEntity or QueueableCollection in ModelIdentifierNormalizer.');
        }
    
        return $this->getSerializedPropertyValue($object);
    }
    
    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, string $format = null)
    {
        return ($data instanceof QueueableEntity || $data instanceof QueueableCollection);
    }
    
    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, string $format = null, array $context = [])
    {
        $identifier = $data instanceof ModelIdentifier
            ? $data
            : new ModelIdentifier($data['class'], $data['id'], $data['relations'], $data['connection']);
        
        return $this->getRestoredPropertyValue($identifier);
    }
    
    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, string $format = null)
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
}
