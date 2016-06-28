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

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverInterface;

final class PartsResolver implements ValueResolverInterface
{
    /**
     * @var ValueResolverInterface
     */
    private $resolver;

    public function __construct(ValueResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope = []
    ): ResolvedFixtureSet
    {
        if (is_array($value)) {
            $resolvedArray = [];
            foreach ($value as $unresolvedValue) {
                $resolvedArray[] = $this->resolve($unresolvedValue, $fixture, $fixtureSet, $scope);
            }

            return $resolvedArray;
        }

        if (is_string($value) === false) {
            return $value;
        }



        $parts = ...;

        foreach ($parts as $part) {
            $this->resolver->resolve();
        }

        return implode();
    }
}
