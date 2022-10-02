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

use Faker\Generator as FakerGenerator;
use function mt_rand;
use Nelmio\Alice\Definition\Value\OptionalValue;
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
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueExceptionFactory;

final class OptionalValueResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    /**
     * @var FakerGenerator
     */
    private $faker;

    public function __construct(ValueResolverInterface $resolver = null, FakerGenerator $faker = null)
    {
        $this->resolver = $resolver;
        $this->faker = $faker;
    }
    
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        return new self($resolver, $this->faker);
    }
    
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof OptionalValue;
    }

    /**
     * @param OptionalValue $value
     *
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

        $quantifier = $value->getQuantifier();
        if ($quantifier instanceof ValueInterface) {
            $resolvedSet = $this->resolver->resolve($quantifier, $fixture, $fixtureSet, $scope, $context);
            [$quantifier, $fixtureSet] = [$resolvedSet->getValue(), $resolvedSet->getSet()];

            if (false === is_int($quantifier) && false === is_string($quantifier)) {
                throw UnresolvableValueExceptionFactory::createForInvalidResolvedQuantifierTypeForOptionalValue($value, $quantifier);
            }
        }

        $realValue = $this->resolveRealValue($value, (int) $quantifier);
        if ($realValue instanceof ValueInterface) {
            return $this->resolver->resolve($realValue, $fixture, $fixtureSet, $scope, $context);
        }

        return new ResolvedValueWithFixtureSet($realValue, $fixtureSet);
    }

    /**
     * @return ValueInterface|mixed
     */
    private function resolveRealValue(ValueInterface $value, int $quantifier)
    {
        // TODO: keeping mt_rand for BC purposes. The generator should be made
        //   non-nullable in 4.x and mt_rand usage removed
        $random = null !== $this->faker ? $this->faker->numberBetween(0, 99) : mt_rand(0, 99);

        return ($random < $quantifier)
            ? $value->getFirstMember()  // @phpstan-ignore-line
            : $value->getSecondMember() // @phpstan-ignore-line
        ;
    }
}
