<?php

/*
 * This file is part of the Nelmio Fixture package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Fixture;

interface LoaderInterface
{
    /**
     * Loads a fixture file
     *
     * @param string       $file filename
     */
    public function load($file);

    /**
     * Returns a reference to a fixture by name
     *
     * @param string $name
     * @return object
     */
    public function getReference($name);

    /**
     * Returns all references created by the loader
     *
     * @return array[object]
     */
    public function getReferences();
}
