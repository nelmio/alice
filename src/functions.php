<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (false === function_exists('deep_clone')) {
    /**
     * Deep clone the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    function deep_clone($value)
    {
        return (new \DeepCopy\DeepCopy())->copy($value);
    }
}
