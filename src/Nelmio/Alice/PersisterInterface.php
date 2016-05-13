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

interface PersisterInterface
{
    /**
     * Persists objects into the database.
     *
     * @param object[] $objects
     */
    public function persist(array $objects);

    /**
     * Finds an object by class and id.
     *
     * @param string $class
     * @param int    $id
     *
     * @return object|null
     */
    public function find($class, $id);
}
