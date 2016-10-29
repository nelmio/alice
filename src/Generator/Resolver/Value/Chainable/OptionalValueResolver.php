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

use Nelmio\Alice\Definition\Value\OptionalValue;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException;
use Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class OptionalValueResolver implements ChainableValueResolverInterface, ValueResolverAwareInterface
{
    use NotClonableTrait;

    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    public function __construct(ValueResolverInterface $resolver = null)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function withValueResolver(ValueResolverInterface $resolver): self
    {
        return new self($resolver);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(ValueInterface $value): bool
    {
        return $value instanceof OptionalValue;
    }

    /**
     * {@inheritdoc}
     *
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
    ): ResolvedValueWithFixtureSet
    {
        if (null === $this->resolver) {
            throw ResolverNotFoundException::createUnexpectedCall(__METHOD__);
        }

        $quantifier = $value->getQuantifier();
        if ($quantifier instanceof ValueInterface) {
            $resolvedSet = $this->resolver->resolve($quantifier, $fixture, $fixtureSet, $scope, $context);
            list($quantifier, $fixtureSet) = [$resolvedSet->getValue(), $resolvedSet->getSet()];

            if (is_int($quantifier)) {
                throw new UnresolvableValueException(
                    sprintf(
                        'Expected the quantifier "%s" for the optional value to be resolved into a string, got "%s" '
                        .'instead.',
                        get_class($value->getQuantifier()),
                        is_object($quantifier) ? get_class($quantifier) : gettype($quantifier)
                    )
                );
            }
        }

        $realValue = (mt_rand(0, 100) <= $quantifier)
            ? $value->getFirstMember()
            : $value->getSecondMember()
        ;
        if ($realValue instanceof ValueInterface) {
            return $this->resolver->resolve($realValue, $fixture, $fixtureSet, $scope, $context);
        }

        return new ResolvedValueWithFixtureSet($realValue, $fixtureSet);
    }
}
