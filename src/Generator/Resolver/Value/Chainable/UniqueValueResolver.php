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

use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\Resolver\UniqueValueGenerationLimit;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\UniqueValuesPool;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class UniqueValueResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

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

    public function __construct(UniqueValuesPool $pool, ValueResolverInterface $resolver = null, int $limit = 5)
    {
        $this->pool = $pool;
        $this->resolver = $resolver;
        if ($limit < 1) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected limit value to be a strictly positive integer, got "%d" instead.',
                    $limit
                )
            );
        }
        $this->limit = $limit;
    }

    /**
     * @inheritdoc
     */
    public function withResolver(ValueResolverInterface $resolver): self
    {
        return new self($this->pool, $resolver);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof UniqueValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param UniqueValue $value
     *
     * @throws UniqueValueGenerationLimit
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope = [],
        int $tryCounter = null
    ): ResolvedValueWithFixtureSet
    {
        $this->checkResolver(__METHOD__);
        $tryCounter = $this->incrementCounter($tryCounter);
        if ($tryCounter >= $this->limit) {
            throw UniqueValueGenerationLimit::create($value, $this->limit);
        }

        $realValue = $value->getValue();
        if ($realValue instanceof ValueInterface) {
            $result = $this->resolver->resolve($value->getValue(), $fixture, $fixtureSet, $scope);
            list($generatedValue, $fixtureSet) = [$value->withValue($result->getValue()), $result->getSet()];
        } else {
            $generatedValue = $value;
        }

        if ($this->pool->has($generatedValue)) {
            return $this->resolve($value, $fixture, $fixtureSet, $scope, $tryCounter);
        }
        $this->pool->add($generatedValue);

        return new ResolvedValueWithFixtureSet($generatedValue->getValue(), $fixtureSet);
    }

    private function checkResolver(string $checkedMethod)
    {
        if (null === $this->resolver) {
            throw new \BadMethodCallException(
                sprintf(
                    'Expected method "%s" to be called only if it has a resolver.',
                    $checkedMethod
                )
            );
        }
    }

    private function incrementCounter(int $tryCounter = null): int
    {
        if (null !== $tryCounter) {
            return $tryCounter + 1;
        }

        return 0;
    }
}
