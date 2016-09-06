<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Hydrator;

use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\Throwable\ResolutionThrowable;

final class SimpleHydrator implements HydratorInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

    /**
     * @var PropertyHydratorInterface
     */
    private $hydrator;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    public function __construct(PropertyHydratorInterface $hydrator, ValueResolverInterface $resolver = null)
    {
        $this->hydrator = $hydrator;
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function withResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->hydrator, $resolver);
    }

    /**
     * @inheritdoc
     */
    public function hydrate(
        ObjectInterface $object,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ResolvedFixtureSet
    {
        if (null === $this->resolver) {
            throw ResolverNotFoundException::createUnexpectedCall(__METHOD__);
        }

        $fixture = $fixtureSet->getFixtures()->get($object->getId());
        $properties = $fixture->getSpecs()->getProperties();

        $scope = [
            '_instances' => $fixtureSet->getObjects()->toArray(),
        ];
        foreach ($properties as $property) {
            /** @var Property $property */
            $propertyValue = $property->getValue();
            if ($propertyValue instanceof ValueInterface) {
                $result = $this->resolver->resolve($propertyValue, $fixture, $fixtureSet, $scope, $context);
                list($propertyValue, $fixtureSet) = [$result->getValue(), $result->getSet()];
                $property = $property->withValue($propertyValue);
            }
            $scope[$property->getName()] = $propertyValue;

            $object = $this->hydrator->hydrate($object, $property, $context);
        }

        return $fixtureSet->withObjects(
            $fixtureSet->getObjects()->with($object)
        );
    }
}
