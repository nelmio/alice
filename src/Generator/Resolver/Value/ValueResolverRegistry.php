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

namespace Nelmio\Alice\Generator\Resolver\Value;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;

final class ValueResolverRegistry implements ValueResolverInterface, ObjectGeneratorAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var ChainableValueResolverInterface[]
     */
    private $resolvers;

    /**
     * @param ChainableValueResolverInterface[] $resolvers
     * @param ObjectGeneratorInterface|null     $generator
     */
    public function __construct(array $resolvers, ObjectGeneratorInterface $generator = null)
    {
        $this->resolvers = (
            function (ObjectGeneratorInterface $generator = null, ChainableValueResolverInterface ...$resolvers) {
                foreach ($resolvers as $index => $resolver) {
                    if ($resolver instanceof ValueResolverAwareInterface) {
                        $resolvers[$index] = $resolver = $resolver->withValueResolver($this);
                    }

                    if (null !== $generator && $resolver instanceof ObjectGeneratorAwareInterface) {
                        /** @var ObjectGeneratorAwareInterface $resolver */
                        $resolvers[$index] = $resolver->withObjectGenerator($generator);
                    }
                }

                return $resolvers;
            }
        )($generator, ...$resolvers);
    }

    /**
     * @inheritdoc
     */
    public function withObjectGenerator(ObjectGeneratorInterface $generator)
    {
        return new self($this->resolvers, $generator);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ResolverNotFoundException
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->canResolve($value)) {
                return $resolver->resolve($value, $fixture, $fixtureSet, $scope, $context);
            }
        }

        throw ResolverNotFoundExceptionFactory::createForValue($value);
    }
}
