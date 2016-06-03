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

use Nelmio\Alice\Throwable\BuildThrowable;

interface FixtureBuilderInterface
{
    /**
     * Parses a given file and build a comprehensive set of data from it and the injected parameters and objects.
     *
     * @param string $file       File to load.
     * @param array  $parameters Additional parameters to inject.
     * @param array  $objects    Additional objects to inject.
     *
     * @throws BuildThrowable
     *
     * @return FixtureSet Contains the loaded parameters, fixtures and the injected parameters, objects.
     */
    public function build(string $file, array $parameters = [], array $objects = []): FixtureSet;
}
