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

interface ParameterBagResolverInterface
{
    /**
     * Resolves a collection of parameters.
     *
     * External parameters can be injected in the process; It is assumed that injected parameters are already resolved
     * and they will be included in the resulting parameter bag.
     *
     * @param ParameterBag      $unresolvedParameters
     * @param ParameterBag|null $injectedParameters
     *
     * @return ParameterBag
     */
    public function resolve(ParameterBag $unresolvedParameters, ParameterBag $injectedParameters = null): ParameterBag;
}
