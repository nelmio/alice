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
use Nelmio\Alice\Exception\Generator\ObjectGenerator\ObjectGeneratorNotFoundException;
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

final class FixtureReferenceResolver
implements ChainableValueResolverInterface, ObjectGeneratorAwareInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

    /**
     * @var ObjectGeneratorInterface|null
     */
    private $generator;

    /**
     * @var ValueResolverInterface|null
     */
    private $resolver;

    public function __construct(ObjectGeneratorInterface $generator = null, ValueResolverInterface $resolver = null)
    {
        $this->generator = $generator;
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function withGenerator(ObjectGeneratorInterface $generator): self
    {
        return new self($generator, $this->resolver);
    }

    /**
     * @inheritdoc
     */
    public function withResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->generator, $resolver);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof FixtureReferenceValue;
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
        array $scope = []
    ): ResolvedValueWithFixtureSet
    {
        $this->checkState(__METHOD__);
        list($referredFixtureId, $fixtureSet) = $this->getReferredFixtureId(
            $this->resolver,
            $value,
            $fixture,
            $fixtureSet,
            $scope
        );

        $referredFixture = $this->getReferredFixture($referredFixtureId, $fixtureSet);
        if (false === $fixtureSet->getObjects()->has($referredFixture)) {
            $objects = $this->generator->generate($referredFixture, $fixtureSet, new GenerationContext());

            $fixtureSet = new ResolvedFixtureSet(
                $fixtureSet->getParameters(),
                $fixtureSet->getFixtures(),
                $objects
            );
        }

        return new ResolvedValueWithFixtureSet(
            $fixtureSet->getObjects()->get($referredFixture)->getInstance(),
            $fixtureSet
        );
    }

    private function checkState(string $method)
    {
        if (null === $this->generator) {
            throw ObjectGeneratorNotFoundException::createUnexpectedCall($method);
        }
        if (null === $this->resolver) {
            throw ResolverNotFoundException::createUnexpectedCall($method);
        }
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
