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

class Custom implements MethodInterface
{
    /**
     * {@inheritDoc}
     */
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        return $fixture->hasCustomSetter();
    }

    /**
     * {@inheritDoc}
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        @trigger_error(
            'Customer setters are deprecated since 2.3.0 and will be removed in 3.0.0.',
            E_USER_DEPRECATED
        );

        if (!method_exists($object, $fixture->getCustomSetter())) {
            throw new \RuntimeException('Setter ' . $fixture->getCustomSetter() . ' not found in object');
        }
        $customSetter = $fixture->getCustomSetter()->getValue();
        $object->$customSetter($property, $value);
    }
}
