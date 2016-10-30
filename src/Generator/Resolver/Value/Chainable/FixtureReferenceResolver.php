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

use Nelmio\Alice\Definition\Fixture\FixtureId;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\FixtureNotFoundException;
use Nelmio\Alice\Exception\Generator\ObjectGenerator\ObjectGeneratorNotFoundException;
use Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\FixtureIdInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class FixtureReferenceResolver implements ChainableValueResolverInterface, ObjectGeneratorAwareInterface
{
    use NotClonableTrait;

    /**
     * @var ObjectGeneratorInterface|null
     */
    private $generator;

    public function __construct(ObjectGeneratorInterface $generator = null)
    {
        $this->generator = $generator;
    }

    /**
     * @inheritdoc
     */
    public function withObjectGenerator(ObjectGeneratorInterface $generator): self
    {
        return new self($generator);
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
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet
    {
        if (null === $this->generator) {
            throw ObjectGeneratorNotFoundException::createUnexpectedCall(__METHOD__);
        }

        $referredFixtureId = $value->getValue();
        if ($referredFixtureId instanceof ValueInterface) {
            throw UnresolvableValueException::create($value);
        }

        $referredFixture = $this->getReferredFixture($referredFixtureId, $fixtureSet);
        if (false === $fixtureSet->getObjects()->has($referredFixture)) {
            if (false === $referredFixture instanceof FixtureInterface) {
                throw FixtureNotFoundException::create($referredFixtureId);
            }

            $context->markIsResolvingFixture($referredFixtureId);
            $objects = $this->generator->generate($referredFixture, $fixtureSet, $context);

            $fixtureSet = $fixtureSet->withObjects($objects);
        }

        return new ResolvedValueWithFixtureSet(
            $fixtureSet->getObjects()->get($referredFixture)->getInstance(),
            $fixtureSet
        );
    }

    private function getReferredFixture(string $id, ResolvedFixtureSet $set): FixtureIdInterface
    {
        $fixtures = $set->getFixtures();
        if ($fixtures->has($id)) {
            return $fixtures->get($id);
        }

        return new FixtureId($id);
    }
}
