<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition;

use Traversable;

final class PropertyBag implements \IteratorAggregate
{
    /**
     * @var Property[]
     */
    private $properties = [];

    public function with(Property $property): self
    {
        $clone = clone $this;
        $clone->properties[$property->getName()] = $property;
        
        return $clone;
    }

    /**
     * Creates a new instance to which the given properties have been merged. In case of conflicts, the existing values
     * are kept.
     *
     * @param PropertyBag $propertyBag
     *
     * @return PropertyBag
     */
    public function mergeWith(self $propertyBag): self
    {
        $clone = clone $propertyBag;
        foreach ($this->properties as $name => $property) {
            $clone->properties[$name] = $property;
        }
        
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator(array_values($this->properties));
    }
}
