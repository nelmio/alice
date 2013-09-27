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

class TestORM implements ORMInterface
{
    protected $objects;
    protected $currentId;

    public function persist(array $objects)
    {
        foreach ($objects as $object) {
            $this->setObjectId($object, ++$this->currentId);
            $this->objects[] = $object;
        }
    }

    public function getObjects()
    {
        return $this->objects;
    }

    protected function getObjectId($object)
    {
        if (property_exists($object, 'id')) {
            return $object->id;
        } elseif (methodExists($object, 'getId')) {
            return $object->getId();
        }
    }

    protected function setObjectId($object, $id)
    {
        if (property_exists($object, 'id')) {
            $object->id = $id;
        } elseif (methodExists($object, 'setId')) {
            $object->setId($id);
        }
    }

    /**
     * @param  string $class
     * @param  int    $id
     * @return mixed
     */
    public function find($class, $id)
    {
        foreach ($this->objects as $object) {
            if ($this->getObjectId($object) == $id) {
                return $object;
            }
        }

        return null;
    }
}
