<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Generator\Hydrator\Property;

use function enum_exists;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\HydrationException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\HydrationExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\InaccessiblePropertyException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\InvalidArgumentException;
use Nelmio\Alice\Throwable\Exception\Generator\Hydrator\NoSuchPropertyException;
use ReflectionEnum;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use Symfony\Component\PropertyAccess\Exception\AccessException as SymfonyAccessException;
use Symfony\Component\PropertyAccess\Exception\ExceptionInterface as SymfonyPropertyAccessException;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException as SymfonyInvalidArgumentException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException as SymfonyNoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use TypeError;

final class SymfonyPropertyAccessorHydrator implements PropertyHydratorInterface
{
    use IsAServiceTrait;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @throws NoSuchPropertyException
     * @throws InaccessiblePropertyException
     * @throws InvalidArgumentException When the typehint does not match for example
     * @throws HydrationException
     */
    public function hydrate(ObjectInterface $object, Property $property, GenerationContext $context): ObjectInterface
    {
        $instance = $object->getInstance();

        try {
            $this->propertyAccessor->setValue($instance, $property->getName(), $property->getValue());
        } catch (SymfonyNoSuchPropertyException $exception) {
            throw HydrationExceptionFactory::createForCouldNotHydrateObjectWithProperty($object, $property, 0, $exception);
        } catch (SymfonyAccessException $exception) {
            throw HydrationExceptionFactory::createForInaccessibleProperty($object, $property, 0, $exception);
        } catch (SymfonyInvalidArgumentException $exception) {
            // as a fallback check if the property might be an enum
            if (
                null !== ($enumType = self::getEnumType($instance, $property))
                && null !== $newProperty = self::castValueToEnum($enumType, $property)
            ) {
                return $this->hydrate($object, $newProperty, $context);
            }

            throw HydrationExceptionFactory::createForInvalidProperty($object, $property, 0, $exception);
        } catch (SymfonyPropertyAccessException $exception) {
            throw HydrationExceptionFactory::create($object, $property, 0, $exception);
        } catch (TypeError $error) {
            throw HydrationExceptionFactory::createForInvalidProperty($object, $property, 0, $error);
        }

        return new SimpleObject($object->getId(), $instance);
    }

    private static function getEnumType($instance, Property $property): ?ReflectionType
    {
        try {
            $enumType = (new ReflectionProperty($instance, $property->getName()))->getType();
        } catch (ReflectionException) {
            // property might not exist
            return null;
        }

        if (null === $enumType) {
            // might not have a type
            return null;
        }

        if ($enumType instanceof ReflectionNamedType
            && !enum_exists($enumType->getName())
        ) {
            // might not be an enum
            return null;
        }

        return $enumType;
    }

    private static function castValueToEnum(ReflectionType $enumType, Property $property): ?Property
    {
        if (!$enumType instanceof ReflectionNamedType) {
            return null;
        }

        $reflectionEnumBackedCases = (new ReflectionEnum($enumType->getName()))->getCases();

        foreach ($reflectionEnumBackedCases as $reflectionCase) {
            $caseValue = $reflectionCase->getValue()->value ?? $reflectionCase->getValue()->name;

            if ($property->getValue() === ($caseValue)) {
                return $property->withValue($reflectionCase->getValue());
            }
        }

        return null;
    }
}
