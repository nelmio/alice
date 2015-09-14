<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances;

/**
 * All methods except #find and #random are copied from Doctrine\Common\Collections\ArrayCollection,
 * to avoid a hard dependency. See ArrayCollection for attribution.
 */
class Collection
{
    /**
     * An array containing the entries of this collection.
     *
     * @var array
     */
    private $instances;

    /**
     * Initializes a new ArrayCollection.
     *
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->instances = $elements;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->instances;
    }

    /**
     * {@inheritDoc}
     */
    public function containsKey($name)
    {
        return isset($this->instances[$name]) || array_key_exists($name, $this->instances);
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $instance)
    {
        $this->instances[$name] = $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($name)
    {
        if (isset($this->instances[$name]) || array_key_exists($name, $this->instances)) {
            $removed = $this->instances[$name];
            unset($this->instances[$name]);

            return $removed;
        }

        return null;
    }

    /**
     * returns an object, or a property on that object if $property is not null
     *
     * @param  string $name
     * @param  string $property
     * @return mixed
     */
    public function find($name, $property = null)
    {
        if ($this->containsKey($name)) {
            $object = $this->get($name);

            if ($property !== null) {
                if (property_exists($object, $property)) {
                    $prop = new \ReflectionProperty($object, $property);

                    if ($prop->isPublic()) {
                        return $object->{$property};
                    }
                }

                $getter = 'get'.ucfirst($property);
                if (method_exists($object, $getter) && is_callable([$object, $getter])) {
                    return $object->$getter();
                }

                throw new \UnexpectedValueException('Property '.$property.' is not defined for instance '.$name);
            }

            return $object;
        }

        throw new \UnexpectedValueException('Instance '.$name.' is not defined');
    }

    /**
     * returns a random object or objects from the collection, or a property on that object if $property is not null
     *
     * @param  string  $mask
     * @param  integer $count
     * @param  string  $property
     * @return mixed
     */
    public function random($mask, $count = 1, $property = null)
    {
        if ($count === 0) {
            return [];
        }

        $availableObjects = array_values(
            preg_grep(
                '{^'.str_replace('*', '.+', $mask).'$}',
                array_keys($this->instances)
            )
        );

        if (!$availableObjects) {
            throw new \UnexpectedValueException('Instance mask "'.$mask.'" did not match any existing instance, make sure the object is created after its references');
        }

        if (null === $count) {
            return $this->find($availableObjects[mt_rand(0, count($availableObjects) - 1)], $property);
        }

        $res = [];
        while ($count-- && $availableObjects) {
            $ref = array_splice($availableObjects, mt_rand(0, count($availableObjects) - 1), 1);
            $res[] = $this->find(current($ref), $property);
        }

        return $res;
    }

    /**
     * Clears the collection, removing all elements.
     */
    public function clear()
    {
        $this->instances = [];
    }
}
