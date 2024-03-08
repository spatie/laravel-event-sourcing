<?php

namespace Spatie\EventSourcing\Support;

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
        return $this->normalizer->extractAttributes($object, $format, $context);
    }

    protected function getAttributeValue(object $object, string $attribute, ?string $format = null, array $context = []): mixed
    {
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
}
