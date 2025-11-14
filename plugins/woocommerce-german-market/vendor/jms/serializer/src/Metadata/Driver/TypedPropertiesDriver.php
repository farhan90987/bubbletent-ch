<?php
/**
 * @license MIT
 *
 * Modified by MarketPress GmbH on 16-October-2024 using {@see https://github.com/BrianHenryIE/strauss}.
 */

declare(strict_types=1);

namespace MarketPress\German_Market\JMS\Serializer\Metadata\Driver;

use MarketPress\German_Market\JMS\Serializer\Metadata\ClassMetadata as SerializerClassMetadata;
use MarketPress\German_Market\JMS\Serializer\Metadata\ExpressionPropertyMetadata;
use MarketPress\German_Market\JMS\Serializer\Metadata\PropertyMetadata;
use MarketPress\German_Market\JMS\Serializer\Metadata\StaticPropertyMetadata;
use MarketPress\German_Market\JMS\Serializer\Metadata\VirtualPropertyMetadata;
use MarketPress\German_Market\JMS\Serializer\Type\Parser;
use MarketPress\German_Market\JMS\Serializer\Type\ParserInterface;
use MarketPress\German_Market\Metadata\ClassMetadata;
use MarketPress\German_Market\Metadata\Driver\DriverInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;

class TypedPropertiesDriver implements DriverInterface
{
    /**
     * @var DriverInterface
     */
    protected $delegate;

    /**
     * @var ParserInterface
     */
    protected $typeParser;

    /**
     * @var string[]
     */
    private $allowList;

    /**
     * @param string[] $allowList
     */
    public function __construct(DriverInterface $delegate, ?ParserInterface $typeParser = null, array $allowList = [])
    {
        $this->delegate = $delegate;
        $this->typeParser = $typeParser ?: new Parser();
        $this->allowList = array_merge($allowList, $this->getDefaultWhiteList());
    }

    private function getDefaultWhiteList(): array
    {
        return [
            'int',
            'float',
            'bool',
            'boolean',
            'string',
            'double',
            'iterable',
            'resource',
        ];
    }

    /**
     * @return SerializerClassMetadata|null
     */
    public function loadMetadataForClass(ReflectionClass $class): ?ClassMetadata
    {
        $classMetadata = $this->delegate->loadMetadataForClass($class);

        if (null === $classMetadata) {
            return null;
        }

        \assert($classMetadata instanceof SerializerClassMetadata);

        // We base our scan on the internal driver's property list so that we
        // respect any internal allow/blocklist like in the AnnotationDriver
        foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
            // If the inner driver provides a type, don't guess anymore.
            if ($propertyMetadata->type) {
                continue;
            }

            try {
                $reflectionType = $this->getReflectionType($propertyMetadata);

                if ($this->shouldTypeHint($reflectionType)) {
                    $type = $reflectionType->getName();

                    $propertyMetadata->setType($this->typeParser->parse($type));
                }
            } catch (ReflectionException $e) {
                continue;
            }
        }

        return $classMetadata;
    }

    private function getReflectionType(PropertyMetadata $propertyMetadata): ?ReflectionType
    {
        if ($this->isNotSupportedVirtualProperty($propertyMetadata)) {
            return null;
        }

        if ($propertyMetadata instanceof VirtualPropertyMetadata) {
            return (new ReflectionMethod($propertyMetadata->class, $propertyMetadata->getter))
                ->getReturnType();
        }

        return (new ReflectionProperty($propertyMetadata->class, $propertyMetadata->name))
            ->getType();
    }

    private function isNotSupportedVirtualProperty(PropertyMetadata $propertyMetadata): bool
    {
        return $propertyMetadata instanceof StaticPropertyMetadata
            || $propertyMetadata instanceof ExpressionPropertyMetadata;
    }

    /**
     * @phpstan-assert-if-true \ReflectionNamedType $reflectionType
     */
    private function shouldTypeHint(?ReflectionType $reflectionType): bool
    {
        if (!$reflectionType instanceof ReflectionNamedType) {
            return false;
        }

        if (in_array($reflectionType->getName(), $this->allowList, true)) {
            return true;
        }

        return class_exists($reflectionType->getName())
            || interface_exists($reflectionType->getName());
    }
}
