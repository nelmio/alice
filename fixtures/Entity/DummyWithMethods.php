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

class DummyWithMethods
{
    private $foo1;
    private $foo2;
    private $bar1;
    private $bar2;
    private $baz1;
    private $baz2;
    private $baz3;

    public function __construct(string $foo1, string $foo2)
    {
        $this->foo1 = $foo1;
        $this->foo2 = $foo2;
    }

    public static function create(string $foo1, string $foo2)
    {
        return new self($foo1, $foo2);
    }

    public function bar(string $bar1, string $bar2)
    {
        $this->bar1 = $bar1;
        $this->bar2 = $bar2;
    }

    public function methodWithVariadic(string $baz1, string $baz2, array ...$baz3)
    {
        $this->baz1 = $baz1;
        $this->baz2 = $baz2;
        $this->baz3 = $baz3;
    }

    public function methodWithDefaultValues(string $baz1 = 'value 1', string $baz2 = 'value 2', string $baz3 = 'value 3')
    {
        $this->baz1 = $baz1;
        $this->baz2 = $baz2;
        $this->baz3 = $baz3;
    }

    public function methodWithNullables(?string $bar1, ?string $bar2)
    {
        $this->bar1 = $bar1;
        $this->bar2 = $bar2;
    }
}
