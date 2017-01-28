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

namespace Nelmio\Alice\Generator\Caller;

use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\ObjectInterface;

final class DummyCaller implements CallerInterface
{
    /**
     * {@inheritdoc}
     */
    public function doCallsOn(ObjectInterface $object, ResolvedFixtureSet $fixtureSet, GenerationContext $context): ResolvedFixtureSet
    {
        return $fixtureSet;
    }
}
