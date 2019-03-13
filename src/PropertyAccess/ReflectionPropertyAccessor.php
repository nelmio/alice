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

namespace Nelmio\Alice\PropertyAccess;

use Closure;
use Nelmio\Alice\IsAServiceTrait;
use ReflectionClass;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Decorator that fallbacks to reflection in case a property cannot be reached another way.
 */
final class ReflectionPropertyAccessor implements PropertyAccessorInterface
{
    use IsAServiceTrait;

    /**
     * @var PropertyAccessorInterface
     */
    private $decoratedPropertyAccessor;

    public function __construct(PropertyAccessorInterface $decoratedPropertyAccessor)
    {
        $this->decoratedPropertyAccessor = $decoratedPropertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(&$objectOrArray, $propertyPath, $value)
    {
        try {
            $this->decoratedPropertyAccessor->setValue($objectOrArray, $propertyPath, $value);
        } catch (NoSuchPropertyException $exception) {
            $propertyReflectionProperty = $this->getPropertyReflectionProperty($objectOrArray, $propertyPath);
            if (null === $propertyReflectionProperty) {
                throw $exception;
            }

            if ($propertyReflectionProperty->getDeclaringClass()->getName() !== get_class($objectOrArray)) {
                $propertyReflectionProperty->setAccessible(true);

                $propertyReflectionProperty->setValue($objectOrArray, $value);

                return;
            }

            $setPropertyClosure = Closure::bind(
                function ($object) use ($propertyPath, $value) {
                    $object->{$propertyPath} = $value;
                },
                $objectOrArray,
                $objectOrArray
            );

            $setPropertyClosure($objectOrArray);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($objectOrArray, $propertyPath)
    {
        try {
            return $this->decoratedPropertyAccessor->getValue($objectOrArray, $propertyPath);
        } catch (NoSuchPropertyException $exception) {
            $propertyReflectionProperty = $this->getPropertyReflectionProperty($objectOrArray, $propertyPath);
            if (null === $propertyReflectionProperty) {
                throw $exception;
            }

            if ($propertyReflectionProperty->getDeclaringClass()->getName() !== get_class($objectOrArray)) {
                $propertyReflectionProperty->setAccessible(true);

                return $propertyReflectionProperty->getValue($objectOrArray);
            }

            $getPropertyClosure = Closure::bind(
                function ($object) use ($propertyPath) {
                    return $object->{$propertyPath};
                },
                $objectOrArray,
                $objectOrArray
            );

            return $getPropertyClosure($objectOrArray);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable($objectOrArray, $propertyPath)
    {
        return $this->decoratedPropertyAccessor->isWritable($objectOrArray, $propertyPath) || $this->propertyExists($objectOrArray, $propertyPath);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable($objectOrArray, $propertyPath)
    {
        return $this->decoratedPropertyAccessor->isReadable($objectOrArray, $propertyPath) || $this->propertyExists($objectOrArray, $propertyPath);
    }

    /**
     * @param object|array $objectOrArray
     * @param string       $propertyPath
     *
     * @return bool Whether the property exists or not.
     */
    private function propertyExists($objectOrArray, $propertyPath)
    {
        return null !== $this->getPropertyReflectionProperty($objectOrArray, $propertyPath);
    }

    private function getPropertyReflectionProperty($objectOrArray, $propertyPath)
    {
        if (false === is_object($objectOrArray)) {
            return null;
        }

        $reflectionClass = (new ReflectionClass(get_class($objectOrArray)));
        while ($reflectionClass instanceof ReflectionClass) {
            if ($reflectionClass->hasProperty($propertyPath)
                && false === $reflectionClass->getProperty($propertyPath)->isStatic()
            ) {
                return $reflectionClass->getProperty($propertyPath);
            }

            $reflectionClass = $reflectionClass->getParentClass();
        }

        return null;
    }
}
