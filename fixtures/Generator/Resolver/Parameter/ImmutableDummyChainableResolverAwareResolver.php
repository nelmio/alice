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

namespace Nelmio\Alice\Generator\Resolver\Parameter;

use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Nelmio\Alice\NotCallableTrait;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;

final class ImmutableDummyChainableResolverAwareResolver implements ChainableParameterResolverInterface, ParameterResolverAwareInterface
{
    use NotCallableTrait;

    /**
     * @var ParameterResolverInterface
     */
    public $resolver;

    public function canResolve(Parameter $parameter): bool
    {
        $this->__call(__FUNCTION__, func_get_args());
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
    ): ParameterBag {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
