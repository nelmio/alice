<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver\Parameter;

use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Resolver\ParameterResolverInterface;

/**
 * To be used for clone tests only.
 */
final class DummyParameterResolverInterface implements ParameterResolverInterface
{
    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters
    ): ParameterBag
    {
        throw new \BadMethodCallException();
    }
}
