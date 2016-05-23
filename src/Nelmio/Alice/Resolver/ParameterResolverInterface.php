<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver;

use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;

interface ParameterResolverInterface
{
    /**
     * Resolves a parameter value.
     *
     * @param Parameter    $parameter Unresolved parameter
     * @param ParameterBag $unresolvedParameters
     * @param ParameterBag $resolvedParameters
     *
     * @return ParameterBag Contains all the resolved parameters (as parameter are dynamics, resolving 1 parameter may
     *                      result in resolving several other parameters).
     */
    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters
    ): ParameterBag;
}
