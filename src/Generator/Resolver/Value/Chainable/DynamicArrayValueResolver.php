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

use Nelmio\Alice\Definition\Value\DynamicArrayValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

final class DynamicArrayValueResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ValueResolverInterface|null
     */
    private $resolver;

    public function __construct(ValueResolverInterface $resolver = null)
    {
        $this->resolver = $resolver;
    }
    
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        return new self($resolver);
    }
    
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof DynamicArrayValue;
    }

    /**
     * @param DynamicArrayValue $value
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet {
        $this->checkResolver(__METHOD__);

        $quantifier = $value->getQuantifier();
        if ($quantifier instanceof ValueInterface) {
            $result = $this->resolver->resolve($quantifier, $fixture, $fixtureSet, $scope, $context);
            [$quantifier, $fixtureSet] = [$result->getValue(), $result->getSet()];
        }

        if ($quantifier < 0) {
            throw InvalidArgumentExceptionFactory::createForInvalidDynamicArrayQuantifier($fixture, $quantifier);
        }

        $element = $value->getElement();
        if (false === $element instanceof ValueInterface) {
            $array = array_fill(0, $quantifier, $element);

            return new ResolvedValueWithFixtureSet($array, $fixtureSet);
        }

        $array = [];
        for ($i = 0; $i < $quantifier; $i++) {
            $result = $this->resolver->resolve($element, $fixture, $fixtureSet, $scope, $context);

            $array[] = $result->getValue();
            $fixtureSet = $result->getSet();
        }

        return new ResolvedValueWithFixtureSet($array, $fixtureSet);
    }

    private function checkResolver(string $checkedMethod): void
    {
        if (null === $this->resolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall($checkedMethod);
        }
    }
}
