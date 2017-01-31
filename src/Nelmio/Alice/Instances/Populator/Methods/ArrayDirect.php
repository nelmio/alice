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

class ArrayDirect implements MethodInterface
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
        return is_array($value) && method_exists($object, $property);
    }

    /**
     * {@inheritDoc}
     */
    public function set(Fixture $fixture, $object, $property, $value)
    {
        @trigger_error(
            'Using a method call is deprecated since 2.3.0. This feature is will be removed in 3.0.0 in favour of'
            .' __calls.',
            E_USER_DEPRECATED
        );

        foreach ($value as $index => $param) {
            $value[$index] = $this->typeHintChecker->check($object, $property, $param, $index);
        }
        call_user_func_array([$object, $property], $value);
    }
}
