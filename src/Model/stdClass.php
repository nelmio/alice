<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Model;

/**
 * Extends stdclass to support alice Populator.
 * @TODO: see if is needed or should be removed
 */
final class stdClass extends \stdClass
{
    /**
     * @param string $method    Method name
     * @param array  $arguments Arguments of the method
     */
    function __call($method, $arguments)
    {
        $property = preg_replace('/^set/', '', $method);
        $this->$property = $arguments[0];
    }
}
