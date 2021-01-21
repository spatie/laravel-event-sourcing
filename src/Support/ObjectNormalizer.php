<?php

namespace Spatie\EventSourcing\Support;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer as SymfonyObjectNormalizer;

class ObjectNormalizer extends SymfonyObjectNormalizer
{
    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, PropertyAccessorInterface $propertyAccessor = null, PropertyTypeExtractorInterface $propertyTypeExtractor = null, ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null, callable $objectClassResolver = null, array $defaultContext = [])
    {
        parent::__construct(
            $classMetadataFactory,
            $nameConverter,
            $propertyAccessor,
            $propertyTypeExtractor ?? new PhpDocExtractor(),
            $classDiscriminatorResolver,
            $objectClassResolver,
            $defaultContext
        );
    }
}
