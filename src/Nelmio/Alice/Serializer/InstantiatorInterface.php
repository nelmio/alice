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
use Nelmio\Alice\ResolvedFixture;

interface InstantiatorInterface
{
    /**
     * Creates and returns an instance of the described class in the fixture.
     *
     * @param ResolvedFixture $fixture
     *
     * @return InstantiatedFixture Unpopulated object
     */
    public function instantiate(ResolvedFixture $fixture): InstantiatedFixture;
}
