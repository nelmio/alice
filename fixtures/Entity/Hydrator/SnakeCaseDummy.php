<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Entity\Hydrator;

class SnakeCaseDummy
{
    public $public_property;
    private $setter_property;
    private $property_with_private_setter;
    private $property_with_protected_setter;

    public function set_setter_property($val)
    {
        $this->setter_property = $val;
    }

    private function set_property_with_private_setter($propertyWithPrivateSetter)
    {
        $this->property_with_private_setter = $propertyWithPrivateSetter;
    }

    protected function set_property_with_protected_setter($propertyWithProtectedSetter)
    {
        $this->property_with_protected_setter = $propertyWithProtectedSetter;
    }
}
