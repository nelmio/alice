<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Populator\Methods;

use Nelmio\Alice\Fixtures\Fixture;

interface MethodInterface
{
    /**
     * returns true if the method is able to set the property to the value on the object described by the given fixture.
     *
     * @param Fixture $fixture
     * @param mixed   $object
     * @param string  $property
     * @param mixed   $value
     *
     * @return bool
     */
    public function canSet(Fixture $fixture, $object, $property, $value);

    /**
     * sets the property to the value on the object described by the given fixture.
     *
     * @param Fixture $fixture
     * @param mixed   $object
     * @param string  $property
     * @param mixed   $value
     */
    public function set(Fixture $fixture, $object, $property, $value);
}
