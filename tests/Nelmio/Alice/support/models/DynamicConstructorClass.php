<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\support\models;

class DynamicConstructorClass
{
    public $alpha;

    public function __construct()
    {
        $arguments = func_get_args();
        $this->alpha = $arguments[0];
    }
}
