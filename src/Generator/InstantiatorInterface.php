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

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\InstantiationThrowable;

interface InstantiatorInterface
{
    /**
     * Instantiates the object described by the given fixture. Has access to the current fixture set and returns the new
     * fixture set containing the instantiated the object.
     *
     *
     * @throws InstantiationThrowable
     */
    public function instantiate(
        FixtureInterface $fixture,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context
    ): ResolvedFixtureSet;
}
