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

use Nelmio\Alice\Definition\Value\ArrayValue;
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

final class ArrayValueResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ValueResolverInterface
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
        return $value instanceof ArrayValue;
    }

    /**
     * @param ArrayValue $list
     */
    public function resolve(
        ValueInterface $list,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet {
        if (null === $this->resolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall(__METHOD__);
        }

        $values = $list->getValue();
        foreach ($values as $index => $value) {
            if ($value instanceof ValueInterface) {
                $resolvedSet = $this->resolver->resolve($value, $fixture, $fixtureSet, $scope, $context);

                $values[$index] = $resolvedSet->getValue();
                $fixtureSet = $resolvedSet->getSet();
            }
        }

        return new ResolvedValueWithFixtureSet($values, $fixtureSet);
    }
}
