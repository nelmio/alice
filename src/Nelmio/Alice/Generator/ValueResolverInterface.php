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

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\ResolutionThrowable;

interface ValueResolverInterface
{
    /**
     * Resolves a value, i.e. determine the final value. Once resolved, the value will be ready to be passed to the
     * object.
     *
     * @param string             $value
     * @param FixtureInterface   $fixture Fixture to which belongs the arguments.
     * @param ResolvedFixtureSet $fixtureSet
     * @param array              $scope   List of variables accessible while resolving the arguments.
     *
     * @throws ResolutionThrowable
     *
     * @return ResolvedValueWithFixtureSet
     */
    public function resolve($value, FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet, array $scope = []): ResolvedValueWithFixtureSet;
}
