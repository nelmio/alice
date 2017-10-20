<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice;

use DeepCopy\DeepCopy;

if (false === function_exists('Nelmio\Alice\deep_clone')) {
    /**
     * Deep clone the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    function deep_clone($value)
    {
        return (new DeepCopy())->copy($value);
    }
}
