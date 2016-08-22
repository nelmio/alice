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

use Faker\Provider\Base;
use Nelmio\Alice\Definition\Value\FixtureMatchReferenceValue;
use Nelmio\Alice\Definition\Value\FixtureReferenceValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\Resolver\ResolutionException;
use Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectInterface;

final class FixtureWildcardReferenceResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    public function __construct(ValueResolverInterface $resolver = null)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function withResolver(ValueResolverInterface $resolver): self
    {
        return new self($resolver);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof FixtureMatchReferenceValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param FixtureMatchReferenceValue $value
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
        if (null === $this->resolver) {
            throw ResolverNotFoundException::createUnexpectedCall(__METHOD__);
        }

        $possibleIds = $this->getSuitableIds($value, $fixtureSet);
        $id = Base::randomElement($possibleIds);
        if (null === $id) {
            throw new ResolutionException(
                sprintf(
                    'Could not find a fixture or object ID matching the pattern "%s".',
                    $value->getValue()
                )
            );
        }

        return $this->resolver->resolve(
            new FixtureReferenceValue($id),
            $fixture,
            $fixtureSet,
            $scope
        );
    }

    /**
     * Gets all the fixture IDs suitable for the given value.
     *
     * @param ResolvedFixtureSet $fixtureSet
     *
     * @return string[]
     */
    private function getSuitableIds(FixtureMatchReferenceValue $value, ResolvedFixtureSet $fixtureSet): array
    {
        $ids = [];

        $fixtures = $fixtureSet->getFixtures();
        foreach ($fixtures as $fixture) {
            /** @var FixtureInterface $fixture */
            $id = $fixture->getId();
            if ($value->match($id)) {
                $ids[$id] = true;
            }
        }

        $objects = $fixtureSet->getObjects();
        foreach ($objects as $object) {
            /** @var ObjectInterface $object */
            $id = $object->getReference();
            if ($value->match($id)) {
                $ids[$id] = true;
            }
        }

        return array_keys($ids);
    }
}
