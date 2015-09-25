<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\support\extensions;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Populator\Methods\MethodInterface as PopulatorInterface;

class CustomPopulator implements PopulatorInterface
{
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        return preg_match('/Contact/', $fixture->getClass());
    }

    /**
     * this custom populator uses magic methods to set properties.
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        return $object->$property = $value;
    }
}
