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
        return method_exists($object, $this->setterFor($property));
    }

    /**
     * {@inheritDoc}
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        $setter = $this->setterFor($property);
        $value = $this->typeHintChecker->check($object, $setter, $value);

        if (!is_callable([$object, $setter])) {
            $refl = new \ReflectionMethod($object, $setter);
            $refl->setAccessible(true);
            $refl->invoke($object, $value);
        } else {
            $object->{$setter}($value);
        }
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
