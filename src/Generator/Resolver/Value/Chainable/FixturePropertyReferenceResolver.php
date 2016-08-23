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

use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\Resolver\NoSuchPropertyException;
use Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException as SymfonyNoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class FixturePropertyReferenceResolver
implements ChainableValueResolverInterface, ObjectGeneratorAwareInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var ObjectGeneratorInterface|null
     */
    private $generator;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        ValueResolverInterface $resolver = null,
        ObjectGeneratorInterface $generator = null
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->resolver = $resolver;
        $this->generator = $generator;
    }

    /**
     * @inheritdoc
     */
    public function withResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->propertyAccessor, $resolver, $this->generator);
    }

    /**
     * @inheritdoc
     */
    public function withGenerator(ObjectGeneratorInterface $generator): self
    {
        return new self($this->propertyAccessor, $this->resolver, $generator);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof FixturePropertyValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param FixturePropertyValue $value
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

        $fixtureReferenceResult = $this->resolver->resolve($value->getReference(), $fixture, $fixtureSet, $scope);
        /** @var ResolvedFixtureSet $fixtureSet */
        list($instance, $fixtureSet) = [$fixtureReferenceResult->getValue(), $fixtureReferenceResult->getSet()];

        try {
            $propertyValue = $this->propertyAccessor->getValue($instance, $value->getProperty());
        } catch (SymfonyNoSuchPropertyException $exception) {
            throw NoSuchPropertyException::createForFixture($fixture, $value, 0, $exception);
        } catch (\Exception $exception) {
            throw UnresolvableValueException::create($value, 0, $exception);
        }

        return new ResolvedValueWithFixtureSet(
            $propertyValue,
            $fixtureSet
        );
    }
}
