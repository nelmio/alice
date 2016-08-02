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

class PascalCaseDummy
{
    public $PublicProperty;
    private $SetterProperty;
    private $PropertyWithPrivateSetter;
    private $PropertyWithProtectedSetter;

    public function SetSetterProperty($val)
    {
        $this->SetterProperty = $val;
    }

    private function SetPropertyWithPrivateSetter($propertyWithPrivateSetter)
    {
        $this->PropertyWithPrivateSetter = $propertyWithPrivateSetter;
    }

    protected function SetPropertyWithProtectedSetter($propertyWithProtectedSetter)
    {
        $this->PropertyWithProtectedSetter = $propertyWithProtectedSetter;
    }
}
