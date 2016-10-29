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

use Nelmio\Alice\Throwable\BuildThrowable;

interface FixtureBuilderInterface
{
    /**
     * Builds a comprehensive set of data from it and the injected parameters and objects.
     *
     * @param array $data       Data to build
     * @param array $parameters Additional parameters to inject
     * @param array $objects    Additional objects to inject
     *
     * @throws BuildThrowable
     *
     * @return FixtureSet Contains the loaded parameters, fixtures and the injected parameters, objects.
     */
    public function build(array $data, array $parameters = [], array $objects = []): FixtureSet;
}
