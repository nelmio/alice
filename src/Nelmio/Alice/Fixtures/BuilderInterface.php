<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures;

use Nelmio\Alice\Fixtures\Definition\UnresolvedFixtureDefinition;
use Nelmio\Alice\Throwable\Fixtures\BuilderThrowable;

interface BuilderInterface
{
    /**
     * @param  UnresolvedFixtureDefinition[] $definitions
     *
     * @throws BuilderThrowable
     *
     * @return Fixture[]
     */
    public function build(array $definitions): array;
}
