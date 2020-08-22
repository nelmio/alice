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

use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\ResolutionThrowable;

interface ParameterBagResolverInterface
{
    /**
     * Resolves a collection of parameters.
     *
     * External parameters can be injected in the process; It is assumed that injected parameters are already resolved
     * and they will be included in the resulting parameter bag.
     *
     *
     * @throws ResolutionThrowable
     */
    public function resolve(ParameterBag $unresolvedParameters, ParameterBag $injectedParameters = null): ParameterBag;
}
