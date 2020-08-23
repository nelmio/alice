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

use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\UniqueValuesPool;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedExceptionFactory;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

final class UniqueValueResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var UniqueValuesPool
     */
    private $pool;

    /**
     * @var ValueResolverInterface|null
     */
    private $resolver;

    /**
     * @var int
     */
    private $limit;

    public function __construct(UniqueValuesPool $pool, ValueResolverInterface $resolver = null, int $limit = 150)
    {
        $this->pool = $pool;
        $this->resolver = $resolver;
        if ($limit < 1) {
            throw InvalidArgumentExceptionFactory::createForInvalidLimitValue($limit);
        }

        $this->limit = $limit;
    }
    
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->pool, $resolver);
    }
    
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof UniqueValue;
    }

    /**
     * @param UniqueValue $value
     *
     * @throws UniqueValueGenerationLimitReachedException
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context,
        int $tryCounter = 0
    ): ResolvedValueWithFixtureSet {
        $this->checkResolver(__METHOD__);
        $tryCounter = $this->incrementCounter($tryCounter, $value, $this->limit);

        /**
         * @var UniqueValue        $generatedValue
         * @var ResolvedFixtureSet $fixtureSet
         */
        [$generatedValue, $fixtureSet] = $this->generateValue(
            $value,
            $fixture,
            $fixtureSet,
            $scope,
            $context
        );

        if ($this->pool->has($generatedValue)) {
            return $this->resolve($value, $fixture, $fixtureSet, $scope, $context, $tryCounter);
        }

        $this->pool->add($generatedValue);

        return new ResolvedValueWithFixtureSet($generatedValue->getValue(), $fixtureSet);
    }

    private function checkResolver(string $checkedMethod): void
    {
        if (null === $this->resolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall($checkedMethod);
        }
    }

    private function incrementCounter(int $tryCounter, UniqueValue $value, int $limit): int
    {
        ++$tryCounter;
        if ($tryCounter > $limit) {
            throw UniqueValueGenerationLimitReachedExceptionFactory::create($value, $limit);
        }

        return $tryCounter;
    }

    private function generateValue(
        UniqueValue $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): array {
        $realValue = $value->getValue();
        if ($realValue instanceof ValueInterface) {
            $result = $this->resolver->resolve($value->getValue(), $fixture, $fixtureSet, $scope, $context);

            return [$value->withValue($result->getValue()), $result->getSet()];
        }

        return [$value, $fixtureSet];
    }
}
