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

use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\ObjectGenerator\ObjectGeneratorNotFoundException;
use Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\UniqueValuesPool;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
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
    public function withGenerator(ObjectGeneratorInterface $generator)
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
     * @throws UniqueValueGenerationLimitReachedException
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope = [],
        int $tryCounter = 0
    ): ResolvedValueWithFixtureSet
    {
        $referredFixtureId = $value->getValue();
        $referredFixture = $fixtureSet->getFixtures()->get($referredFixtureId);

        if (null === $this->generator) {
            ObjectGeneratorNotFoundException::create(__METHOD__);
        }
        $objects = $this->generator->generate($referredFixture, $fixtureSet);

        $fixtureSet = new ResolvedFixtureSet(
            $fixtureSet->getParameters(),
            $fixtureSet->getFixtures(),
            $objects
        );

        return new ResolvedValueWithFixtureSet(
            $fixtureSet->getObjects()->get($referredFixture)->getInstance(),
            $fixtureSet
        );
    }
}
