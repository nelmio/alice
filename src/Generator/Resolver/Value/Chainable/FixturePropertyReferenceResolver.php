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

use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\NoSuchPropertyException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\NoSuchPropertyExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueExceptionFactory;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException as SymfonyNoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class FixturePropertyReferenceResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    public function __construct(PropertyAccessorInterface $propertyAccessor, ValueResolverInterface $resolver = null)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->propertyAccessor, $resolver);
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
     * @throws NoSuchPropertyException
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

        $context->markAsNeedsCompleteGeneration();
        $fixtureReferenceResult = $this->resolver->resolve($value->getReference(), $fixture, $fixtureSet, $scope, $context);
        $context->unmarkAsNeedsCompleteGeneration();

        /** @var ResolvedFixtureSet $fixtureSet */
        list($instance, $fixtureSet) = [$fixtureReferenceResult->getValue(), $fixtureReferenceResult->getSet()];

        try {
            $propertyValue = $this->propertyAccessor->getValue($instance, $value->getProperty());
        } catch (SymfonyNoSuchPropertyException $exception) {
            throw NoSuchPropertyExceptionFactory::createForFixture($fixture, $value, 0, $exception);
        } catch (\Exception $exception) {
            throw UnresolvableValueExceptionFactory::create($value, 0, $exception);
        }

        return new ResolvedValueWithFixtureSet(
            $propertyValue,
            $fixtureSet
        );
    }
}
