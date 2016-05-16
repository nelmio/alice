<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator\Methods;

use Nelmio\Alice\Fixtures\Fixture;

interface MethodInterface
{
    /**
     * Returns true if this method can instantiate the object described in the fixture.
     *
     * @param Fixture $fixture
     *
     * @return bool
     */
    public function canInstantiate(Fixture $fixture);

    /**
     * Returns an empty instance of the class the fixture describes. This method should be called only if
     * ::canInstantiate() returns true.
     *
     * @param Fixture $fixture
     *
     * @return object
     */
    public function instantiate(Fixture $fixture);
}
