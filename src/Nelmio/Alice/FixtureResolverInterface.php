<?php

/*
 * This file is part of the Alice package.
<<<<<<< 6162463343bedc82a568baaa631a63133683c9fd
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
=======
 *
 * (c) Nelmio <hello@nelm.io>
 *
>>>>>>> WIP
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

use Nelmio\Alice\Resolver\Fixture\ResolvingContext;

interface FixtureResolverInterface
{
    /**
     * Resolves a collection of fixtures.
     *
     * External objects can be injected in the process; If an injected object has the same reference as a created
     * fixture, it will be overridden by the new object.
     *
     * @param UnresolvedFixtureInterface $fixture
     * @param ParameterBag               $parameters
     * @param UnresolvedFixtureBag       $fixtures
     * @param ObjectBag                  $objects
     * @param ResolvingContext           $context
     *
     * @return FixtureResolutionResult
     */
    public function resolve(
        UnresolvedFixtureInterface $fixture,
        ParameterBag $parameters,
        UnresolvedFixtureBag $fixtures,
        ObjectBag $objects,
        ResolvingContext $context
    ): FixtureResolutionResult;
}
