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

use Nelmio\Alice\Throwable\PopulationThrowable;

interface PopulatorInterface
{
    /**
     * Populates a given object. Has access to the current fixture set and returns the new fixture set containing the
     * populated object.
     *
     * @param UnpopulatedObject  $object Object to populate
     * @param ResolvedFixtureSet $fixtureSet
     *
     * @throws PopulationThrowable
     *
     * @return ResolvedFixtureSet
     */
    public function populate(UnpopulatedObject $object, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet;
}
