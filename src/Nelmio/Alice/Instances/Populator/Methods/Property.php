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

class Property implements MethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        return property_exists($object, $property);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        $refl = new \ReflectionProperty($object, $property);
        $refl->setAccessible(true);
        $refl->setValue($object, $value);
    }
}
