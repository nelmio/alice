<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Hydrator;

use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class PropertyAccessorHydrator implements HydratorInterface
{
    use NotClonableTrait;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccess;

    public function __construct(PropertyAccessorInterface $propertyAccess)
    {
        $this->propertyAccess = $propertyAccess;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(ObjectInterface $object, Property $property): ObjectInterface
    {
        $instance = $object->getInstance();
        $this->propertyAccess->setValue($instance, $property->getName(), $property->getValue());

        return new SimpleObject($object->getReference(), $instance);
    }
}
