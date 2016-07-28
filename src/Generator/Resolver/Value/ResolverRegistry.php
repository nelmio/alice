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
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\NotClonableTrait;

final class ResolverRegistry implements ValueResolverInterface
{
    use NotClonableTrait;

    /**
     * @var ChainableValueResolverInterface[]
     */
    private $resolvers;

    /**
     * @param ChainableValueResolverInterface[] $resolvers
     */
    public function __construct(array $resolvers)
    {
        $this->resolvers = (function (ChainableValueResolverInterface ...$resolvers) { return $resolvers; })(...$resolvers);
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

        throw new ResolverNotFoundException(
            sprintf(
                'No suitable value resolver found to handle the value "%s".',
                get_class($value)
            )
        );
    }
}
