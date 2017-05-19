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

use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueExceptionFactory;

/**
 * Resolves the fixture reference ID first if it is itself a value before handing over the resolution to the decorated
 * resolver.
 */
final class UnresolvedFixtureReferenceIdResolver implements ChainableValueResolverInterface, ObjectGeneratorAwareInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ChainableValueResolverInterface
     */
    private $decoratedResolver;

    /**
     * @var ValueResolverInterface|null
     */
    private $resolver;

    public function __construct(ChainableValueResolverInterface $decoratedResolver, ValueResolverInterface $resolver = null)
    {
        $this->decoratedResolver = $decoratedResolver;
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function withObjectGenerator(ObjectGeneratorInterface $generator): self
    {
        $decoratedResolver = ($this->decoratedResolver instanceof ObjectGeneratorAwareInterface)
            ? $this->decoratedResolver->withObjectGenerator($generator)
            : $this->decoratedResolver
        ;

        return new self($decoratedResolver, $this->resolver);
    }

    /**
     * @inheritdoc
     */
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        $decoratedResolver = ($this->decoratedResolver instanceof ValueResolverAwareInterface)
            ? $this->decoratedResolver->withValueResolver($resolver)
            : $this->decoratedResolver
        ;

        return new self($decoratedResolver, $resolver);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $this->decoratedResolver->canResolve($value);
    }

    /**
     * {@inheritdoc}
     *
     * @param FixtureReferenceValue $value
     *
     * @throws UnresolvableValueException
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet {
        if (null === $this->resolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall(__METHOD__);
        }

        list($referredFixtureId, $fixtureSet) = $this->getReferredFixtureId(
            $this->resolver,
            $value,
            $fixture,
            $fixtureSet,
            $scope,
            $context
        );

        return $this->decoratedResolver->resolve(
            new FixtureReferenceValue($referredFixtureId),
            $fixture,
            $fixtureSet,
            $scope,
            $context
        );
    }

    /**
     * @throws UnresolvableValueException
     */
    private function getReferredFixtureId(
        ValueResolverInterface $resolver,
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $set,
        array $scope,
        GenerationContext $context
    ): array {
        $referredFixtureId = $value->getValue();
        if ($referredFixtureId instanceof ValueInterface) {
            $resolvedSet = $resolver->resolve($referredFixtureId, $fixture, $set, $scope, $context);

            list($referredFixtureId, $set) = [$resolvedSet->getValue(), $resolvedSet->getSet()];
            if (false === is_string($referredFixtureId)) {
                throw UnresolvableValueExceptionFactory::createForInvalidReferenceId($value, $referredFixtureId);
            }
        }

        return [$referredFixtureId, $set];
    }
}
