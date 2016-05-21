<?php

/**
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver\Parameter;

use Nelmio\Alice\Exception\ParameterNotFoundException;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Resolver\ChainableParameterValueResolverInterface;
use Nelmio\Alice\Resolver\ParameterValueResolverAwareInterface;
use Nelmio\Alice\Resolver\ParameterValueResolverInterface;

final class RecursiveParameterResolver implements ChainableParameterValueResolverInterface, ParameterValueResolverAwareInterface
{
    /**
     * @var ChainableParameterValueResolverInterface
     */
    private $resolver;

    public function __construct(ChainableParameterValueResolverInterface $decoratedResolver)
    {
        $this->resolver = $decoratedResolver;
    }

    public function setResolver(ParameterValueResolverInterface $resolver)
    {
        if ($this->resolver instanceof ParameterValueResolverAwareInterface) {
            $this->resolver->setResolver($resolver);
        }
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
