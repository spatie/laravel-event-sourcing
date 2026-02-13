<?php

namespace Spatie\EventSourcing\Support;

use ReflectionClass;
use Spatie\EventSourcing\StoredEvents\ShouldBeStored;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer as SymfonyAbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer as SymfonyObjectNormalizer;

class ObjectNormalizer extends SymfonyAbstractObjectNormalizer
{
    protected SymfonyObjectNormalizer $normalizer;

    private const SHOULD_BE_STORED_ATTRIBUTES = [
        'metaData',
        'eventVersion',
        'createdAt',
        'aggregateRootUuid',
        'storedEventId',
        'aggregateRootVersion',
    ];

    public function __construct(?ClassMetadataFactoryInterface $classMetadataFactory = null, ?NameConverterInterface $nameConverter = null, ?PropertyAccessorInterface $propertyAccessor = null, ?PropertyTypeExtractorInterface $propertyTypeExtractor = null, ?ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null, ?callable $objectClassResolver = null, array $defaultContext = [])
    {

        $propertyTypeExtractor = $propertyTypeExtractor ?? new PhpDocExtractor();

        $this->normalizer = new SymfonyObjectNormalizer(
            $classMetadataFactory,
            $nameConverter,
            $propertyAccessor,
            $propertyTypeExtractor,
            $classDiscriminatorResolver,
            $objectClassResolver,
            $defaultContext
        );

        parent::__construct(
            $classMetadataFactory,
            $nameConverter,
            $propertyTypeExtractor,
            $classDiscriminatorResolver,
            $objectClassResolver,
            $defaultContext
        );
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['object' => false];
    }

    protected function extractAttributes(object $object, ?string $format = null, array $context = []): array
    {
        $attributes = $this->normalizer->extractAttributes($object, $format, $context);

        if ($object instanceof ShouldBeStored) {
            $attributes = $this->filterShouldBeStoredAttributes($object, $attributes);
        }

        return $attributes;
    }

    protected function getAttributeValue(object $object, string $attribute, ?string $format = null, array $context = []): mixed
    {
        if ($object instanceof ShouldBeStored && in_array($attribute, self::SHOULD_BE_STORED_ATTRIBUTES)) {
            $reflection = new ReflectionClass($object);

            if ($this->childClassDeclaresProperty($reflection, $attribute)) {
                return $reflection->getProperty($attribute)->getValue($object);
            }
        }

        return $this->normalizer->getAttributeValue($object, $attribute, $format, $context);
    }

    protected function setAttributeValue(object $object, string $attribute, mixed $value, ?string $format = null, array $context = []): void
    {
        $this->normalizer->setAttributeValue($object, $attribute, $value, $format, $context);
    }

    protected function getAllowedAttributes(string|object $classOrObject, array $context, bool $attributesAsString = false): array|bool
    {
        return $this->normalizer->getAllowedAttributes($classOrObject, $context, $attributesAsString);
    }

    private function filterShouldBeStoredAttributes(ShouldBeStored $object, array $attributes): array
    {
        $reflection = new ReflectionClass($object);

        return array_values(array_filter($attributes, function (string $attribute) use ($reflection) {
            if (! in_array($attribute, self::SHOULD_BE_STORED_ATTRIBUTES)) {
                return true;
            }

            return $this->childClassDeclaresProperty($reflection, $attribute);
        }));
    }

    private function childClassDeclaresProperty(ReflectionClass $reflection, string $attribute): bool
    {
        if (! $reflection->hasProperty($attribute)) {
            return false;
        }

        return $reflection->getProperty($attribute)->getDeclaringClass()->getName() !== ShouldBeStored::class;
    }
}
