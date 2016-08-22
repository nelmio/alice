<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Value;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ObjectGeneratorAwareInterface;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class ValueResolverRegistry implements ValueResolverInterface, ObjectGeneratorAwareInterface
{
    use NotClonableTrait;

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
                        $resolvers[$index] = $resolver = $resolver->withResolver($this);
                    }

                    if (null !== $generator && $resolver instanceof ObjectGeneratorAwareInterface) {
                        $resolvers[$index] = $resolver->withGenerator($generator);
                    }
                }

                return $resolvers;
            }
        )($generator, ...$resolvers);
    }

    /**
     * @inheritdoc
     */
    public function withGenerator(ObjectGeneratorInterface $generator)
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
        array $scope = []
    ): ResolvedValueWithFixtureSet
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->canResolve($value)) {
                return $resolver->resolve($value, $fixture, $fixtureSet, $scope);
            }
        }

        throw ResolverNotFoundException::createForValue($value);
    }
}
