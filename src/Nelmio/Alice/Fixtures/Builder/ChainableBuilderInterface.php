<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder;

use Nelmio\Alice\Fixtures\BuilderInterface;

interface ChainableBuilderInterface extends BuilderInterface
{
    /**
     * @param  string $name
     *
     * @example:
     *  'user0', 'user{0..10}
     *
     * @return bool
     */
    public function canBuild(string $name): bool;
}
