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

use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\NotClonableTrait;
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
     * Hydrate the object with the provided.
     *
     * @param \object  $object
     * @param Property $property
     *
     * @return \object
     */
    public function hydrate($object, Property $property)
    {
        $this->propertyAccess->setValue($object, $property->getName(), $property->getValue());

        return $object;
    }
}
