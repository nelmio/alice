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
     * returns true if this method can instantiate the object described in the fixture
     *
     * @param Fixture
     * @return boolean
     */
    public function canInstantiate(Fixture $fixture);

    /**
     * returns an empty instance of the class the fixture describes
     *
     * @param Fixture
     * @return mixed
     */
    public function instantiate(Fixture $fixture);
}
