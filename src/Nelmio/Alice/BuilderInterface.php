<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

use Nelmio\Alice\Throwable\BuildThrowable;

interface BuilderInterface
{
    /**
     * Builds the data parsed from the parser into a comprehensive collection
     * of fixtures.
     *
     * @param array $fixtures PHP data coming from the parser
     *
     * @throws BuildThrowable
     *                        
     * @return UnresolvedFixtureSet Contains a collection of parameters and
     *                              fixture objects that needs to be resolved.
     */
    public function build(array $fixtures): UnresolvedFixtureSet;
}
