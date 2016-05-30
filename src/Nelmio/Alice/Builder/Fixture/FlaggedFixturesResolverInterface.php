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

use Nelmio\Alice\UnresolvedFixtureBag;

interface FlaggedFixturesResolverInterface
{
    /**
     * Go through all the fixtures to resolve the flags.
     *
     * @param UnresolvedFixtureBag $fixtures
     *
     * @return UnresolvedFixtureBag
     */
    public function resolve(UnresolvedFixtureBag $fixtures): UnresolvedFixtureBag;
}
