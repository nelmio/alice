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

use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\Throwable\ResolutionThrowable;

interface SpecsResolverInterface
{
    /**
     * Resolves the specifications of a given fixture. Has access to the current fixture set and returns the fixture
     * with its resolved specifications.
     *
     * @param string             $reference Reference of the fixture to which belongs the specifications.
     * @param SpecificationBag   $specs
     * @param ResolvedFixtureSet $fixtureSet
     *
     * @throws ResolutionThrowable
     * 
     * @return ResolvedFixtureSet
     */
    public function resolve(string $reference, SpecificationBag $specs, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet;
}
