<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Nelmio\Alice\PropertyAccess;

use Nelmio\Alice\IsAServiceTrait;
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
        } catch (NoSuchPropertyException $e) {
            if (!$this->propertyExists($objectOrArray, $propertyPath)) {
                throw $e;
            }

            $setPropertyClosure = \Closure::bind(
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
        } catch (NoSuchPropertyException $e) {
            if (!$this->propertyExists($objectOrArray, $propertyPath)) {
                throw $e;
            }

            $getPropertyClosure = \Closure::bind(
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
        if (!is_object($objectOrArray)) {
            return false;
        }

        $reflectionClass = (new \ReflectionClass(get_class($objectOrArray)));

        return $reflectionClass->hasProperty($propertyPath) && !$reflectionClass->getProperty($propertyPath)->isStatic();
    }
}
