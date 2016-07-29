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
use Nelmio\Alice\Exception\ObjectNotFoundException;

/**
 * Value object containing a list of objects.
 */
final class ObjectBag implements \IteratorAggregate, \Countable
{
    /**
     * @var ObjectInterface[]
     */
    private $objects = [];

    public function __construct(array $objects = [])
    {
        foreach ($objects as $reference => $object) {
            if ($object instanceof ObjectInterface) {
                $object = $object->getInstance();
            }

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
        foreach ($objects->objects as $reference => $object) {
            $clone->objects[$reference] = $object;
        }
        
        return $clone;
    }
    
    public function has(FixtureInterface $fixture): bool
    {
        return isset($this->objects[$fixture->getId()]);
    }

    /**
     * @param FixtureInterface $fixture
     *
     * @throws ObjectNotFoundException
     * 
     * @return ObjectInterface
     */
    public function get(FixtureInterface $fixture): ObjectInterface
    {
        if ($this->has($fixture)) {
            return $this->objects[$fixture->getId()];
        }
        
        throw ObjectNotFoundException::create($fixture->getId(), $fixture->getClassName());
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->objects);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->objects);
    }

    public function toArray(): array
    {
        $array = [];
        foreach ($this->objects as $reference => $object) {
            $array[$reference] = $object->getInstance();
        }

        return $array;
    }
}
