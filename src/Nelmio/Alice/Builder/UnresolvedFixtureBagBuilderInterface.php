<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder;

use Nelmio\Alice\BuilderInterface;
use Nelmio\Alice\UnresolvedFixtureBag;

interface UnresolvedFixtureBagBuilderInterface
{
    /**
     * A more specific version of {@see Nelmio\Alice\BuilderInterface} dedicated to fixtures.
     *
     * @param array $fixtures PHP data coming from the parser
     *
     * @throws BuilderInterface
     *                        
     * @return UnresolvedFixtureBag Collection of unresolved fixtures
     */
    public function build(array $fixtures): UnresolvedFixtureBag;
}
