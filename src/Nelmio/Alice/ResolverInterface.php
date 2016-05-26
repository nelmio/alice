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

interface ResolverInterface
{
    /**
     * Resolves a list of fixtures. Objects and parameters can be injected in the
     * process. This allows other libraries to easily extend Alice. For example one
     * could decide to split fixtures in two separate files but with one file depending
     * on the fixtures of the other; In that situation he could decide to load them separately
     * and inject the result when necessary.
     *
     * @param UnresolvedFixtureSet $resolvedFixtureSet Fixtures to resolve
     * @param array                $injectedObjects    Collection of object injected
     *                                                 on which the fixtures being resolved
     *                                                 may refer to.
     * @param array                $injectedParameters List of parameters to inject.
     *
     * @return FixtureSet List of resolved fixtures and parameters.
     */
    public function resolve(UnresolvedFixtureSet $resolvedFixtureSet, array $injectedObjects, array $injectedParameters): FixtureSet;
}
