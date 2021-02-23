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

namespace Nelmio\Alice\Entity;

class DummyWithVariadicConstructorParamAndParam
{
    public $val;
    public $variadic;

    public function __construct($val, ...$variadic)
    {
        $this->val = $val;
        $this->variadic = $variadic;
    }
}
