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
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\RecursionLimitReachedException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\RecursionLimitReachedExceptionFactory;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

/**
 * Decorates a chainable resolver to be able to apply it recursively.
 */
final class RecursiveParameterResolver implements ChainableParameterResolverInterface, ParameterResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ChainableParameterResolverInterface
     */
    private $resolver;

    /**
     * @var int
     */
    private $limit;

    public function __construct(ChainableParameterResolverInterface $resolver, int $limit = 5)
    {
        $this->resolver = $resolver;

        if (2 > $limit) {
            throw InvalidArgumentExceptionFactory::createForInvalidLimitValueForRecursiveCalls($limit);
        }

        $this->limit = $limit;
    }
    
    public function withResolver(ParameterResolverInterface $resolver)
    {
        $decoratedResolver = $this->resolver;
        if ($decoratedResolver instanceof ParameterResolverAwareInterface) {
            $decoratedResolver = $decoratedResolver->withResolver($resolver);
        }

        return new self($decoratedResolver);
    }
    
    public function canResolve(Parameter $parameter): bool
    {
        return $this->resolver->canResolve($parameter);
    }

    /**
     * Resolves a parameter two times and if a different result is obtained will resolve the parameter again until two
     * successive resolution give the same result.
     *
     * {@inheritdoc}
     *
     * @throws RecursionLimitReachedException
     */
    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters,
        ResolvingContext $context = null,
        ParameterBag $previousResult = null,
        int $counter = 1
    ): ParameterBag {
        if (null === $previousResult) {
            $result = $this->resolver->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context);

            return $this->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context, $result);
        }

        $parameterKey = $parameter->getKey();
        $previousParameterValue = $previousResult->get($parameterKey);
        $counter = $this->incrementCounter($counter, $this->limit, $parameterKey);

        $newResult = $this->resolver->resolve(
            $parameter->withValue($previousParameterValue),
            $unresolvedParameters,
            $resolvedParameters,
            $context
        );
        $newParameterValue = $newResult->get($parameterKey);
        $result = $this->mergeResults($previousResult, $newResult);

        if ($previousParameterValue === $newParameterValue) {
            return $result;
        }

        return $this->resolve($parameter, $unresolvedParameters, $resolvedParameters, $context, $result, $counter);
    }

    /**
     * @throws RecursionLimitReachedException
     */
    private function incrementCounter(int $counter, int $limit, string $parameterKey): int
    {
        if ($counter >= $limit) {
            throw RecursionLimitReachedExceptionFactory::create($limit, $parameterKey);
        }

        return ++$counter;
    }

    private function mergeResults(ParameterBag $previous, ParameterBag $new): ParameterBag
    {
        foreach ($previous as $key => $value) {
            $new = $new->with(
                new Parameter((string) $key, $value)
            );
        }

        return $new;
    }
}
