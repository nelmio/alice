<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Parameter;

use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;


final class ImmutableDummyChainableResolverAwareResolver implements ChainableParameterResolverInterface, ParameterResolverAwareInterface
{
    public $resolver;

    public function canResolve(Parameter $parameter): bool
    {
        throw new \BadMethodCallException();
    }

    public function withResolver(ParameterResolverInterface $resolver)
    {
        $clone = clone $this;
        $clone->resolver = $resolver;

        return $clone;
    }

    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters
    ): ParameterBag
    {
        throw new \BadMethodCallException();
    }
}

