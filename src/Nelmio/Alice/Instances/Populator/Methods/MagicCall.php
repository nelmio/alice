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

class MagicCall implements MethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        return method_exists($object, '__call');
    }

    /**
     * {@inheritDoc}
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        $setter = $this->setterFor($property);
        $object->{$setter}($value);
    }

    /**
     * return the name of the setter for a given property
     *
     * @param  string $property
     * @return string
     */
    private function setterFor($property)
    {
        return "set{$property}";
    }
}
