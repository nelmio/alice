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

namespace Nelmio\Alice\Generator\Resolver;

use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\ResolutionThrowable;

/**
 * More specific version of {@see \Nelmio\Alice\Generator\Resolver\ParameterBagResolverInterface} to resolve a specific
 * parameter.
 */
interface ParameterResolverInterface
{
    /**
     * Resolves a parameter value.
     *
     * @param Parameter    $parameter Unresolved parameter
     *
     * @throws ResolutionThrowable
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
