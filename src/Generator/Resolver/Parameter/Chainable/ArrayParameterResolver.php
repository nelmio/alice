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

namespace Nelmio\Alice\Generator\Resolver\Parameter\Chainable;

use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;

/**
 * Resolves array parameters.
 */
final class ArrayParameterResolver implements ChainableParameterResolverInterface, ParameterResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ParameterResolverInterface|null
     */
    private $resolver;

    public function __construct(ParameterResolverInterface $resolver = null)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function withResolver(ParameterResolverInterface $resolver): self
    {
        return new self($resolver);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(Parameter $parameter): bool
    {
        return is_array($parameter->getValue());
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Parameter $unresolvedArrayParameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters,
        ResolvingContext $context = null
    ): ParameterBag {
        if (null === $this->resolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall(__METHOD__);
        }

        $context = ResolvingContext::createFrom($context, $unresolvedArrayParameter->getKey());

        $resolvedArray = [];
        /* @var array $unresolvedArray */
        $unresolvedArray = $unresolvedArrayParameter->getValue();
        foreach ($unresolvedArray as $index => $unresolvedValue) {
            // Iterate over all the values of the array to resolve each of them
            $resolvedParameters = $this->resolver->resolve(
                new Parameter((string) $index, $unresolvedValue),
                $unresolvedParameters,
                $resolvedParameters,
                $context
            );

            $resolvedArray[$index] = $resolvedParameters->get((string) $index);
            $resolvedParameters = $resolvedParameters->without((string) $index);
        }

        $resolvedParameters = $resolvedParameters->with(
            $unresolvedArrayParameter->withValue($resolvedArray)
        );

        return $resolvedParameters;
    }
}
