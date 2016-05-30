<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Serializer;

use Nelmio\Alice\InstantiatedFixture;
use Nelmio\Alice\Object;

interface PopulatorInterface
{
    /**
     * Populates all the properties for the object described by the given fixture.
     *
     * @param InstantiatedFixture $fixture
     *
     * @return Object
     */
    public function populate(InstantiatedFixture $fixture): Object;
}
