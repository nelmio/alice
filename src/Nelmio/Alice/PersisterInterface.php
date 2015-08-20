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

/**
 * The persister is the class responsible for persisting objects into the database.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Robert Schönthal
 */
interface PersisterInterface
{
    /**
     * Persists objects to database.
     *
     * @param object[] $objects
     */
    public function persist(array $objects);

    /**
     * Finds an object by class and id.
     *
     * @param  string $class
     * @param  int    $id
     *
     * @return object|null null if object not found.
     */
    public function find($class, $id);
}
