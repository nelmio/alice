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

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\ResolutionThrowable;

interface ArgumentsResolverInterface
{
    /**
     * Resolves an array of arguments, i.e. determine the final value of each argument parameter. Once resolved, the
     * value will be ready to be passed to the object.
     *
     * @param ValueInterface[]|mixed[] $arguments Arguments to resolved
     * @param FixtureInterface         $fixture   Fixture to which belongs the arguments.
     * @param array                    $scope     List of variables accessible while resolving the arguments.
     *
     * @throws ResolutionThrowable
     *
     * @return ResolvedFixtureSet Set where the arguments of the given fixture will have been resolved.
     */
    public function resolve(array $arguments, FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet, array $scope = []): ResolvedFixtureSet;
}
