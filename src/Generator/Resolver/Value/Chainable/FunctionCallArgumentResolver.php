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

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Value\FunctionCallValue;
use Nelmio\Alice\Definition\Value\ResolvedFunctionCallValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;

/**
 * Resolves the argument of a function call before handing over the resolution to the decorated resolver.
 */
final class FunctionCallArgumentResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    /**
     * @var ValueResolverInterface|null
     */
    private $argumentResolver;

    public function __construct(ValueResolverInterface $decoratedResolver, ValueResolverInterface $argumentResolver = null)
    {
        $this->resolver = $decoratedResolver;
        $this->argumentResolver = $argumentResolver;
    }
    
    public function withValueResolver(ValueResolverInterface $argumentsResolver): self
    {
        return new self($this->resolver, $argumentsResolver);
    }
    
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof FunctionCallValue;
    }

    /**
     * @param FunctionCallValue $value
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet {
        if (null === $this->argumentResolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall(__METHOD__);
        }

        $arguments = $value->getArguments();
        foreach ($arguments as $index => $argument) {
            if ($argument instanceof ValueInterface) {
                $resolvedSet = $this->argumentResolver->resolve($argument, $fixture, $fixtureSet, $scope, $context);

                $arguments[$index] = $resolvedSet->getValue();
                $fixtureSet = $resolvedSet->getSet();
            }
        }

        return $this->resolver->resolve(
            new ResolvedFunctionCallValue($value->getName(), $arguments),
            $fixture,
            $fixtureSet,
            $scope,
            $context
        );
    }
}
