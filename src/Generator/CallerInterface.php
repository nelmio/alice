<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\ObjectInterface;

interface CallerInterface
{
    /**
     * Do calls on the already populated object.
     *
     * @param ObjectInterface    $object Populated object
     * @param ResolvedFixtureSet $fixtureSet
     *
     * @return ResolvedFixtureSet Set containing the object on which the calls have been made.
     */
    public function doCallsOn(ObjectInterface $object, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet;
}
