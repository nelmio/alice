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
     * {@inheritDoc}
     */
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        return (bool) $this->findClass($object, $property);
    }

    /**
     * {@inheritDoc}
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        $refl = new \ReflectionProperty($this->findClass($object, $property), $property);
        $refl->setAccessible(true);
        $refl->setValue($object, $value);
    }

    /**
     * Find which class defines the property.
     *
     * @param mixed  $class
     * @param string $property
     */
    private function findClass($class, $property)
    {
        do {
            if (property_exists($class, $property)) {
                return $class;
            }
        } while ($class = get_parent_class($class));
    }
}
