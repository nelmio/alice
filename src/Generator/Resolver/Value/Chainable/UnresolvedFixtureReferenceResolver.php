<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class UnresolvedFixtureReferenceResolver
implements ChainableValueResolverInterface, ObjectGeneratorAwareInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

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
    public function withGenerator(ObjectGeneratorInterface $generator): self
    {
        if ($this->decoratedResolver instanceof ObjectGeneratorAwareInterface) {
            $this->decoratedResolver = $this->decoratedResolver->withGenerator($generator);
        }

        return new self($this->decoratedResolver, $this->resolver);
    }

    /**
     * @inheritdoc
     */
    public function withResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->decoratedResolver, $resolver);
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
    ): ResolvedValueWithFixtureSet
    {
        if (null === $this->resolver) {
            throw ResolverNotFoundException::createUnexpectedCall(__METHOD__);
        }

        list($referredFixtureId, $fixtureSet) = $this->getReferredFixtureId(
            $this->resolver,
            $value,
            $fixture,
            $fixtureSet,
            $scope
        );

        return $this->decoratedResolver->resolve(
            new FixtureReferenceValue($referredFixtureId),
            $fixture,
            $fixtureSet,
            $scope,
            $context
        );
    }

    private function getReferredFixtureId(
        ValueResolverInterface $resolver,
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $set,
        array $scope
    ): array
    {
        $referredFixtureId = $value->getValue();
        if ($referredFixtureId instanceof ValueInterface) {
            $resolvedSet = $resolver->resolve($referredFixtureId, $fixture, $set, $scope);

            list($referredFixtureId, $set) = [$resolvedSet->getValue(), $resolvedSet->getSet()];
            if (false === is_string($referredFixtureId)) {
                throw UnresolvableValueException::create($value);
            }
        }

        return [$referredFixtureId, $set];
    }

    private function getReferredFixture(string $id, ResolvedFixtureSet $set): FixtureInterface
    {
        $fixtures = $set->getFixtures();
        if ($fixtures->has($id)) {
            return $fixtures->get($id);
        }

        return new SimpleFixture(
            $id,
            '',
            new SpecificationBag(
                null,
                new PropertyBag(),
                new MethodCallBag()
            )
        );
    }
}
