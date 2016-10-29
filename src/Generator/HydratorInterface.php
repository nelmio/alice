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

use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\Throwable\HydrationThrowable;

interface HydratorInterface
{
    /**
     * Hydrates the given object. Has access to the current fixture set and returns the new fixture set containing the
     * hydrated object.
     *
     * @param ObjectInterface    $object Object to hydrate
     * @param ResolvedFixtureSet $fixtureSet
     * @param GenerationContext  $context
     *
     * @throws HydrationThrowable
     *
     * @return ResolvedFixtureSet
     */
    public function hydrate(
        ObjectInterface $object,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ResolvedFixtureSet;
}
