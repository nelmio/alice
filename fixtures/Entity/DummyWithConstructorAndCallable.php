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

class DummyWithConstructorAndCallable
{
    private $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    public function reset()
    {
        $this->foo = null;
    }

    public function getFoo()
    {
        return $this->foo;
    }
}
