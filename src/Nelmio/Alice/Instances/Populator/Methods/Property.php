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
     * @inheritDoc
     */
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        return (bool) $this->findClass($object, $property);
    }

    /**
     * @inheritDoc
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        $refl = new \ReflectionProperty($this->findClass($object, $property), $property);
        if (false === $refl->isPublic()) {
            @trigger_error(
                'Setting a private or protected directly is deprecated since 2.3.0 and will be removed in 3.0.0.',
                E_USER_DEPRECATED
            );

        }

        $refl->setAccessible(true);
        $refl->setValue($object, $value);
    }

    /**
     * Finds which class defines the property.
     *
     * @param mixed  $class
     * @param string $property
     *
     * @return string
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
