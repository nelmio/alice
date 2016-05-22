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
use Nelmio\Alice\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Resolver\ParameterResolverInterface;

final class RecursiveParameterResolver implements ChainableParameterResolverInterface, ParameterResolverAwareInterface
{
    /**
     * @var ChainableParameterResolverInterface
     */
    private $resolver;

    public function __construct(ChainableParameterResolverInterface $decoratedResolver)
    {
        $this->resolver = $decoratedResolver;
    }

    public function withResolver(ParameterResolverInterface $resolver)
    {
        $clone = clone $this;
        if ($clone->resolver instanceof ParameterResolverAwareInterface) {
            $clone->resolver = $clone->resolver->withResolver($resolver);
        }
        
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function canResolve(Parameter $parameter): bool
    {
        return $this->resolver->canResolve($parameter);
    }

    /**
     * {@inheritdoc}
     *
     * @param bool|int|float $parameter
     */
    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters,
        ResolvingContext $context = null,
        ParameterBag $previousResult = null
    ): ParameterBag
    {
        if (null === $previousResult) {
            $result = $this->resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

            return $this->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context, $result);
        }
        $previousParameterValue = $previousResult->get($parameter->getKey());

        $currentResult = $this->resolver->resolve(
            $parameter->withValue($previousParameterValue),
            $unresolvedParameters,
            $resolvedParameters,
            $context
        );
        $currentParameterValue = $currentResult->get($parameter->getKey());
        if ($previousParameterValue === $currentParameterValue) {
            return $currentResult;
        }

        return $this->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context, $currentResult);
    }
}
