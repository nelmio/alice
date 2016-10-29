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

interface ValueResolverInterface
{
    /**
     * Resolves a value, i.e. determine the final value. Once resolved, the value will be ready to be passed to the
     * object.
     *
     * @param ValueInterface     $value
     * @param FixtureInterface   $fixture Fixture to which belongs the arguments.
     * @param ResolvedFixtureSet $fixtureSet
     * @param array              $scope   List of variables accessible while resolving the arguments.
     * @param GenerationContext  $context
     *
     * @throws ResolutionThrowable
     *
     * @return ResolvedValueWithFixtureSet
     */
    public function resolve(
        ValueInterface $value,
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        array $scope,
        GenerationContext $context
    ): ResolvedValueWithFixtureSet;
}
