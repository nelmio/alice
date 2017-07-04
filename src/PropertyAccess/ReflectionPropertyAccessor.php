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

use Nelmio\Alice\IsAServiceTrait;
use ReflectionClass;
use ReflectionProperty;
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
            if (false === $this->propertyExists($objectOrArray, $propertyPath)) {
                throw $exception;
            }

            $reflectionProperty = new ReflectionProperty($this->findClass($objectOrArray, $propertyPath), $propertyPath);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($objectOrArray, $value);
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
            if (false === $this->propertyExists($objectOrArray, $propertyPath)) {
                throw $exception;
            }

            $reflectionProperty = new ReflectionProperty($this->findClass($objectOrArray, $propertyPath), $propertyPath);
            return $reflectionProperty->getValue($propertyPath);
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
        if (false === is_object($objectOrArray)) {
            return false;
        }

        $reflectionClass = (new ReflectionClass(get_class($objectOrArray)));

        while ($reflectionClass) {
            if ($reflectionClass->hasProperty($propertyPath)) {
                return false === $reflectionClass->getProperty($propertyPath)->isStatic();
            }

            $reflectionClass = $reflectionClass->getParentClass();
        }

        return false;
    }

    /**
     * Finds which class defines the property.
     *
     * @param mixed  $class
     * @param string $property
     *
     * @return string|null
     */
    private function findClass($class, $property)
    {
        do {
            if (property_exists($class, $property)) {
                return $class;
            }
        } while ($class = get_parent_class($class));
    }
}
