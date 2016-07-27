<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Populator;

use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\PopulatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectInterface;

final class SimplePopulator implements PopulatorInterface
{
    use NotClonableTrait;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    public function __construct(ValueResolverInterface $resolver, HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function populate(ObjectInterface $object, ResolvedFixtureSet $fixtureSet): ResolvedFixtureSet
    {
        $fixture = $fixtureSet->getFixtures()->get($object->getReference());
        $properties = $fixture->getSpecs()->getProperties();

        $scope = [];
        foreach ($properties as $property) {
            /** @var Property $property */
            $resolvedValue = $this->resolver->resolve($property->getValue(), $fixture, $fixtureSet, $scope);
            $scope[$property->getName()] = $resolvedValue;

            $object = $this->hydrator->hydrate($object, $property->withValue($resolvedValue));
        }

        return new ResolvedFixtureSet(
            $fixtureSet->getParameters(),
            $fixtureSet->getFixtures(),
            $fixtureSet->getObjects()->with($object)
        );
    }
}
