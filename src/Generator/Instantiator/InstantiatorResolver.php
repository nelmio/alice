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

namespace Nelmio\Alice\Generator\Instantiator;

use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\InstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationExceptionFactory;
use Nelmio\Alice\Throwable\ResolutionThrowable;

/**
 * Resolves each argument to be passed to the constructor when is relevant before handling over the updated fixture to
 * instantiate to the decorated instantiator.
 */
final class InstantiatorResolver implements InstantiatorInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var InstantiatorInterface
     */
    private $instantiator;

    /**
     * @var ValueResolverInterface|null
     */
    private $valueResolver;

    public function __construct(InstantiatorInterface $instantiator, ValueResolverInterface $valueResolver = null)
    {
        if (null !== $valueResolver && $instantiator instanceof ValueResolverAwareInterface) {
            $instantiator = $instantiator->withValueResolver($valueResolver);
        }

        $this->instantiator = $instantiator;
        $this->valueResolver = $valueResolver;
    }
    
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->instantiator, $resolver);
    }

    /**
     * Resolves the fixture constructor arguments before instantiating it.
     *
     * {@inheritdoc}
     *
     * @throws UnresolvableValueDuringGenerationException
     */
    public function instantiate(
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ResolvedFixtureSet {
        [$fixture, $fixtureSet] = $this->resolveFixtureConstructor($fixture, $fixtureSet, $context);

        return $this->instantiator->instantiate($fixture, $fixtureSet, $context);
    }

    /**
     * @throws UnresolvableValueDuringGenerationException
     */
    private function resolveFixtureConstructor(
        FixtureInterface $fixture,
        ResolvedFixtureSet $set,
        GenerationContext $context
    ): array {
        $specs = $fixture->getSpecs();
        $constructor = $specs->getConstructor();

        if (null === $constructor || $constructor instanceof NoMethodCall) {
            return [$fixture, $set];
        }

        if (null === $this->valueResolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall(__METHOD__);
        }

        [$resolvedArguments, $set] = $this->resolveArguments(
            $constructor->getArguments(),
            $this->valueResolver,
            $fixture,
            $set,
            $context
        );

        return [
            $fixture->withSpecs(
                $specs->withConstructor(
                    $constructor->withArguments($resolvedArguments)
                )
            ),
            $set,
        ];
    }

    /**
     * @throws UnresolvableValueDuringGenerationException
     *
     * @return array The first element is an array ($arguments) which is the resolved arguments and the second the new
     *               ResolvedFixtureSet which may contains new fixtures (from the arguments resolution)
     */
    private function resolveArguments(
        array $arguments,
        ValueResolverInterface $resolver,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): array {
        $scope = $fixtureSet->getParameters()->toArray();

        $argumentPosition = 1;
        foreach ($arguments as $index => $argument) {
            if ($argument instanceof ValueInterface) {
                try {
                    $result = $resolver->resolve($argument, $fixture, $fixtureSet, $scope, $context);
                } catch (ResolutionThrowable $throwable) {
                    throw UnresolvableValueDuringGenerationExceptionFactory::createFromResolutionThrowable($throwable);
                }

                [$fixtureSet, $value] = [$result->getSet(), $result->getValue()];

                $arguments[$index] = $value;

                if (is_int($index)) {
                    $scope[$argumentPosition] = $value;
                } else {
                    $scope[$index] = $value;
                }

                $argumentPosition++;
            }
        }

        return [$arguments, $fixtureSet];
    }
}
