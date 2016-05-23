<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\InstantiatorInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
interface ChainableInstantiatorInterface extends InstantiatorInterface
{
    /**
     * @param  Fixture $fixture
     *
     * @return bool
     */
    public function canInstantiate(Fixture $fixture): bool;
}
