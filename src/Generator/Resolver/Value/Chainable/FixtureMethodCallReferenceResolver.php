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

use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\NoSuchMethodExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\NoSuchPropertyException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueExceptionFactory;
use Throwable;

final class FixtureMethodCallReferenceResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ValueResolverInterface
     */
    private $argumentResolver;

    public function __construct(ValueResolverInterface $resolver = null)
    {
        $this->argumentResolver = $resolver;
    }
    
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        return new self($resolver);
    }
    
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof FixtureMethodCallValue;
    }

    /**
     * @param FixtureMethodCallValue $value
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
        if (null === $this->argumentResolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall(__METHOD__);
        }

        $functionCall = $value->getFunctionCall();
        $arguments = $functionCall->getArguments();
        foreach ($arguments as $index => $argument) {
            if ($argument instanceof ValueInterface) {
                $resolvedSet = $this->argumentResolver->resolve($argument, $fixture, $fixtureSet, $scope, $context);

                $arguments[$index] = $resolvedSet->getValue();
                $fixtureSet = $resolvedSet->getSet();
            }
        }

        $context->markAsNeedsCompleteGeneration();
        $fixtureReferenceResult = $this->argumentResolver->resolve($value->getReference(), $fixture, $fixtureSet, $scope, $context);
        $context->unmarkAsNeedsCompleteGeneration();

        /** @var ResolvedFixtureSet $fixtureSet */
        [$instance, $fixtureSet] = [
            $fixtureReferenceResult->getValue(),
            $fixtureReferenceResult->getSet()
        ];

        try {
            $resolvedValue = $instance->{$functionCall->getName()}(...$arguments);
        } catch (Throwable $exception) {
            if (false === method_exists($instance, $functionCall->getName())) {
                throw NoSuchMethodExceptionFactory::createForFixture($fixture, $value);
            }

            throw UnresolvableValueExceptionFactory::create($value, 0, $exception);
        }

        return new ResolvedValueWithFixtureSet(
            $resolvedValue,
            $fixtureSet
        );
    }
}
