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
use Nelmio\Alice\Util\TypeHintChecker;

class Direct implements MethodInterface
{
    /**
     * @var TypeHintChecker
     */
    protected $typeHintChecker;

    public function __construct(TypeHintChecker $typeHintChecker)
    {
        $this->typeHintChecker = $typeHintChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function canSet(Fixture $fixture, $object, $property, $value)
    {
        return method_exists($object, $this->getPropertySetter($object, $property));
    }

    /**
     * {@inheritDoc}
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        $setter = $this->getPropertySetter($object, $property);
        $value = $this->typeHintChecker->check($object, $setter, $value);

        if (false !== strpos($setter, '_')) {
            @trigger_error(
                'Using a non PSR-2 compliant setter is deprecated since 2.3.0 and will be removed in 3.0.0.',
                E_USER_DEPRECATED
            );
        }

        if (!is_callable([$object, $setter])) {
            // Protected or private method
            $refl = new \ReflectionMethod($object, $setter);
            if (false === $refl->isPublic()) {
                @trigger_error(
                    'Using a private or protected method to set a property is deprecated since 2.3.0 and will be'
                    .' removed in 3.0.0.',
                    E_USER_DEPRECATED
                );
            }
            $refl->setAccessible(true);
            $refl->invoke($object, $value);

            return;
        }

        $object->{$setter}($value);
    }

    /**
     * Returns the name of the setter for a given property.
     *
     * @param object|string $object
     * @param string        $property
     *
     * @return string
     */
    private function getPropertySetter($object, $property)
    {
        $normalizedProperty = str_replace('_', '', $property);
        $setters = [
            "set{$normalizedProperty}" => true,
            "set{$property}" => true,
            "set_{$property}" => true,
            "set_{$normalizedProperty}" => true,
        ];

        foreach ($setters as $setter => $void) {
            if (method_exists($object, $setter)) {
                return $setter;
            }
        }

        return '';
    }
}
