<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

use Nelmio\Alice\Definition\Object\SimpleObject;

/**
 * Value object containing a list of objects.
 */
final class ObjectBag implements \IteratorAggregate
{
    private $objects = [];

    public function __construct(array $objects = [])
    {
        foreach ($objects as $reference => $object) {
            $this->objects[$reference] = new SimpleObject($reference, $object);
        }
    }

    /**
     * Creates a new instance which will contain the given object. If an object with the same reference already exists,
     * it will be overridden by the new object.
     * 
     * @param ObjectInterface $object
     *
     * @return self
     */
    public function with(ObjectInterface $object): self
    {
        $clone = clone $this;
        $clone->objects[$object->getReference()] = $object;
        
        return $clone;
    }

    /**
     * Creates a new instance with the new objects. If objects with the same reference already exists, they will be
     * overridden by the new ones.
     * 
     * @param ObjectBag $objects
     *
     * @return self
     */
    public function mergeWith(self $objects): self
    {
        $clone = clone $this;
        foreach ($objects as $object) {
            /** @var ObjectInterface $object */
            $clone->objects[$object->getReference()] = $object;
        }
        
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->objects);
    }
}
