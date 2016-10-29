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

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\Throwable\ConfigurationThrowable;

interface ConfiguratorInterface
{
    /**
     * Configures a given object. Has access to the current fixture set and returns the new fixture set containing the
     * configured object.
     *
     * @param ObjectInterface    $object Object to configure
     * @param ResolvedFixtureSet $fixtureSet
     *
     * @throws ConfigurationThrowable
     *
     * @return ResolvedFixtureSet
     */
    public function configure(ObjectInterface $object, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet;
}
