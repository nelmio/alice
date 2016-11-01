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

namespace Nelmio\Alice;

use Nelmio\Alice\Definition\Object\CompleteObject;
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
        foreach ($objects as $id => $object) {
            if ($object instanceof ObjectInterface) {
                if ($id !== $object->getId()) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Reference key mismatch, the keys "%s" and "%s" refers to the same fixture.',
                            $id,
                            $object->getId()
                        )
                    );
                }
                $this->objects[$id] = $object;

                continue;
            }

            $this->objects[$id] = new CompleteObject(
                new SimpleObject($id, $object)
            );
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
        $clone->objects[$object->getId()] = $object;

        return $clone;
    }

    /**
     * Creates a new instance which will no longer contain the given object.
     *
     * @param FixtureInterface|ObjectInterface $objectOrFixture
     *
     * @return self
     */
    public function without($objectOrFixture): self
    {
        $clone = clone $this;
        unset($clone->objects[$objectOrFixture->getId()]);

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
    
    public function has(FixtureIdInterface $fixture): bool
    {
        return isset($this->objects[$fixture->getId()]);
    }

    /**
     * @param FixtureIdInterface $fixture
     *
     * @throws ObjectNotFoundException
     * 
     * @return ObjectInterface
     */
    public function get(FixtureIdInterface $fixture): ObjectInterface
    {
        if ($this->has($fixture)) {
            return $this->objects[$fixture->getId()];
        }
        
        throw ObjectNotFoundException::create(
            $fixture->getId(),
            $fixture instanceof FixtureInterface ? $fixture->getClassName() : 'no class given'
        );
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
