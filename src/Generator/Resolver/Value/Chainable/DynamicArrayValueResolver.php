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

use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class DynamicArrayValueResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

    /**
     * @var ValueResolverInterface|null
     */
    private $resolver;

    /**
     * @var int
     */
    private $limit;

    public function __construct(ValueResolverInterface $resolver = null, int $limit = 5)
    {
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
    public function with(ValueResolverInterface $resolver): self
    {
        return new self($resolver, $this->limit);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof DynamicArrayValue;
    }

    /**
     * {@inheritdoc}
     *
     * @param DynamicArrayValue $value
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope = []
    ): ResolvedValueWithFixtureSet
    {
        $this->checkResolver(__METHOD__);

        $quantifier = $value->getQuantifier();
        if ($quantifier instanceof ValueInterface) {
            $result = $this->resolver->resolve($quantifier, $fixture, $fixtureSet, $scope);
            list($quantifier, $fixtureSet) = [$result->getValue(), $result->getSet()];
        }

        if ($quantifier < 2) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected quantifier to be an integer superior or equal to 2. Got "%d" for "%s", check you dynamic'
                    .' arrays declarations (e.g. "<numberBetween(1, 2)>x @user*").',
                    $quantifier,
                    $fixture->getId()
                )
            );
        }

        $element = $value->getElement();
        if (false === $element instanceof ValueInterface) {
            $array = array_fill(0, $quantifier, $element);

            return new ResolvedValueWithFixtureSet($array, $fixtureSet);
        }

        $array = [];
        for ($i = 0; $i < $quantifier; $i++) {
            $result = $this->resolver->resolve($element, $fixture, $fixtureSet, $scope);

            $array[] = $result->getValue();
            $fixtureSet = $result->getSet();
        }

        return new ResolvedValueWithFixtureSet($array, $fixtureSet);
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
}
