<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;

interface LoaderInterface
{
    /**
     * Loads a fixture file.
     *
     * @param string $file filename
     *
     * @return object[]
     */
    public function load($file);

    /**
     * Returns a reference to a fixture by name.
     *
     * @param  string      $name
     * @param  string|null $property optionally return only a given property of the reference
     *
     * @return object
     */
    public function getReference($name, $property = null);

    /**
     * Returns all references created by the loader.
     *
     * @return object[]
     */
    public function getReferences();

    /**
     * @param array $providers
     */
    public function setProviders(array $providers);

    /**
     * @param array $references
     */
    public function setReferences(array $references);

}
