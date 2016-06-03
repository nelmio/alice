<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture;

interface ChainableFixtureBuilderInterface extends UnresolvedFixtureBuilderInterface
{
    /**
     * @param  string $reference
     *
     * @example:
     *  'user0', 'user{0..10}
     *
     * @return bool
     */
    public function canBuild(string $reference): bool;
}
