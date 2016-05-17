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

interface ChainableFixtureBuilderInterface extends FixtureBuilderInterface
{
    /**
     * @param string $name e.g. 'user0' or 'user{1...10}'
     *
     * @return bool
     */
    public function canBuild(string $name): bool;
}
