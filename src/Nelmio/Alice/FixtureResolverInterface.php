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

interface FixtureResolverInterface
{
    /**
     * Resolves a collection of fixtures.
     *
     * External objects can be injected in the process; If an injected object has the same reference as a created
     * fixture.
     *
     * @param ParameterBag         $parameters
     * @param UnresolvedFixtureBag $unresolvedFixtures
     * @param array                $injectedObjects
     *
     * @return FixtureBag
     */
    public function resolve(ParameterBag $parameters, UnresolvedFixtureBag $unresolvedFixtures, array $injectedObjects): FixtureBag;
}
