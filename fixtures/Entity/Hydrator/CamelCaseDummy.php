<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Entity\Hydrator;

class CamelCaseDummy
{
    public $publicProperty;
    private $setterProperty;
    private $propertyWithPrivateSetter;
    private $propertyWithProtectedSetter;

    public function setSetterProperty($val)
    {
        $this->setterProperty = $val;
    }

    private function setPropertyWithPrivateSetter($propertyWithPrivateSetter)
    {
        $this->propertyWithPrivateSetter = $propertyWithPrivateSetter;
    }

    protected function setPropertyWithProtectedSetter($propertyWithProtectedSetter)
    {
        $this->propertyWithProtectedSetter = $propertyWithProtectedSetter;
    }
}
