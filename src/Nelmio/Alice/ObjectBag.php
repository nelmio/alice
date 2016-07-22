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
     * @var array<string, ObjectInterface[]>
     */
    private $objects = [];

    /**
     * @var int
     */
    private $count = 0;

    public function __construct(array $objects = [])
    {
        foreach ($objects as $className => $classObjects) {
            if (false === is_array($classObjects)) {
                throw new \TypeError(
                    sprintf(
                        'Expected array of objects to be an array where keys are FQCN and values arrays of object '
                        .'reference/object pairs but found a "%s" instead of an array.',
                        gettype($classObjects)
                    )
                );
            }
            
            $this->objects[$className] = [];
            foreach ($classObjects as $reference => $object) {
                $this->objects[$className][$reference] = new SimpleObject($reference, $object);
                $this->count++;
            }
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
        $objectClass = get_class($object->getInstance());
        if (false === array_key_exists($objectClass, $clone->objects)) {
            $clone->objects[$objectClass] = [];
        }
        $clone->objects[$objectClass][$object->getReference()] = $object;
        $clone->count++;
        
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
        foreach ($objects as $className => $classObjects) {
            /** @var ObjectInterface[] $classObjects */
            if (false === array_key_exists($className, $clone->objects)) {
                $clone->objects[$className] = $classObjects;
                $clone->count++;
                
                continue;
            }

            foreach ($classObjects as $reference => $object) {
                $clone->objects[$className][$reference] = $object;
                $clone->count++;
            }
        }
        
        return $clone;
    }
    
    public function has(FixtureInterface $fixture): bool
    {
        $className = $fixture->getClassName();
        $reference = $fixture->getReference();
        
        return isset($this->objects[$className][$reference]);
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
        $className = $fixture->getClassName();
        $reference = $fixture->getReference();
        if (isset($this->objects[$className][$reference])) {
            return clone $this->objects[$className][$reference];
        }
        
        throw ObjectNotFoundException::create($reference, $className);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->objects);
    }

    public function toFlatArray(): array
    {
        $flatArray = [];
        foreach ($this->objects as $className => $references) {
            foreach ($references as $reference => $instance) {
                $flatArray[$reference] = $instance->getInstance();
            }
        }

        if (count($flatArray) !== $this->count) {
            foreach ($this->objects as $className => $references) {
                foreach ($references as $reference => $instance) {
                    if (array_key_exists($reference, $flatArray)) {
                        throw new \InvalidArgumentException(
                            'Two objects have the reference "%s".',
                            $reference
                        );
                    }

                    $flatArray[$reference] = $instance->getInstance();
                }
            }
        }

        return $flatArray;
    }
}
