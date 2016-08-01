<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Hydrator\Property;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Exception\Generator\Hydrator\HydrationException;
use Nelmio\Alice\Generator\Hydrator\PropertyHydratorInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectInterface;

final class LegacyPropertyAccessorHydrator implements PropertyHydratorInterface
{
    use NotClonableTrait;

    /**
     * @inheritdoc
     */
    public function hydrate(ObjectInterface $object, Property $property): ObjectInterface
    {
        $instance = $object->getInstance();
        $accessor = $this->getPropertySetter($instance, $property->getName());

        if (is_callable([$object, $accessor])) {
            $instance->{$accessor}($property->getValue());

            new SimpleObject($object->getReference(), $instance);
        }

        // Protected or private method
        try {
            $accessorRefl = new \ReflectionMethod($object, $accessor);
            $accessorRefl->setAccessible(true);
            $accessorRefl->invoke($instance, $property->getValue());
        } catch (\ReflectionException $exception) {
            throw HydrationException::create($object, $property);
        }

        return new SimpleObject($object->getReference(), $instance);
    }

    /**
     * Returns the name of the setter for a given property.
     *
     * @param object $object
     * @param string        $property
     *
     * @return string
     */
    private function getPropertySetter($object, string $property): string
    {
        $normalizedProperty = str_replace('_', '', $property);
        $setters = [
            "set{$normalizedProperty}" => true,
            "set{$property}" => true,
            "set_{$property}" => true,
            "set_{$normalizedProperty}" => true,
        ];

        foreach ($setters as $setter => $void) {
            if (method_exists($object, $setter)) {
                return $setter;
            }
        }

        return '';
    }
}
