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

namespace Nelmio\Alice\Generator\Hydrator;

use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\HydratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationExceptionFactory;
use Nelmio\Alice\Throwable\ResolutionThrowable;

final class SimpleHydrator implements HydratorInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

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
    public function withValueResolver(ValueResolverInterface $resolver): self
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
    ): ResolvedFixtureSet {
        if (null === $this->resolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall(__METHOD__);
        }

        $fixture = $fixtureSet->getFixtures()->get($object->getId());
        $properties = $fixture->getSpecs()->getProperties();

        $scope = $fixtureSet->getParameters()->toArray();
        $scope['_instances'] = $fixtureSet->getObjects()->toArray();

        foreach ($properties as $property) {
            /** @var Property $property */
            $propertyValue = $property->getValue();
            if ($propertyValue instanceof ValueInterface) {
                try {
                    $result = $this->resolver->resolve($propertyValue, $fixture, $fixtureSet, $scope, $context);
                } catch (ResolutionThrowable $throwable) {
                    throw UnresolvableValueDuringGenerationExceptionFactory::createFromResolutionThrowable($throwable);
                }

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
