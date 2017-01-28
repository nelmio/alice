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

interface CallerInterface
{
    /**
     * Do calls on the already hydrated object.
     *
     * @param ObjectInterface    $object     Hydrated object
     * @param ResolvedFixtureSet $fixtureSet
     * @param GenerationContext  $context
     *
     * @return ResolvedFixtureSet Set containing the object on which the calls have been made.
     */
    public function doCallsOn(ObjectInterface $object, ResolvedFixtureSet $fixtureSet, GenerationContext $context): ResolvedFixtureSet;
}
