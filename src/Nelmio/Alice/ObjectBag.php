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

    public function __construct(array $objects)
    {
        foreach ($objects as $reference => $object) {
            $this->objects[$reference] = new SimpleObject($reference, $object);
        }
    }
    
    public function with(ObjectInterface $object): self
    {
        $clone = clone $this;
        $clone[$object->getReference()] = $object;
        
        return $clone;
    }

    public function mergeWith(self $objects): self
    {
        $clone = clone $this;
        foreach ($objects as $object) {
            /** @var ObjectInterface $object */
            $clone[$object->getReference()] = $object;
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
