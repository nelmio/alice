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

class DummyWithGetter
{
    private $foo;
    public $fooVal;

    public function setFoo(string $foo)
    {
        $this->foo = strrev($foo);
    }

    public function getFoo(): string
    {
        return $this->foo;
    }
}
