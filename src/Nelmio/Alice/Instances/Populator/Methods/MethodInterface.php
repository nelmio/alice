<?php

namespace Nelmio\Alice\Instances\Populator\Methods;

use Nelmio\Alice\Fixtures\Fixture;

interface MethodInterface
{
    /**
     * Returns true if the method is able to set the property to the value on the object described by the given fixture.
     *
     * @param  Fixture $fixture
     * @param  mixed   $object
     * @param  string  $property
     * @param  mixed   $value
     *
     * @return boolean
     */
    public function canSet(Fixture $fixture, $object, $property, $value);

    /**
     * Sets the property to the value on the object described by the given fixture.
     *
     * @param Fixture $fixture
     * @param mixed   $object
     * @param string  $property
     * @param mixed   $value
     */
    public function set(Fixture $fixture, $object, $property, $value);
}
