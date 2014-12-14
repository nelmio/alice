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

class TestPersister implements PersisterInterface
{
    protected $objects;

    public function persist(array $objects)
    {
        $this->objects = $objects;
    }

    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @param  string $class
     * @param  int    $id
     * @return mixed
     */
    public function find($class, $id)
    {
        return null;
    }
}
