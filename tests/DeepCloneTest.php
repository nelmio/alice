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

use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers ::\Nelmio\Alice\deep_clone
 */
class DeepCloneTest extends TestCase
{
    /**
     * @dataProvider provideScalarValues
     */
    public function testDeepCloneScalarsReturnsScalar($value): void
    {
        $clone = deep_clone($value);

        static::assertEquals($value, $clone);
    }

    public function testDeepCloneObjects(): void
    {
        $foo = new stdClass();
        $bar = new stdClass();

        $foo->name = 'foo';
        $foo->bar = $bar;
        $foo->date = new DateTime();

        $bar->name = 'bar';
        $bar->foo = $foo;
        $bar->date = new DateTimeImmutable();

        $fooClone = deep_clone($foo);

        $this->assertEqualsButNotSame($foo, $fooClone);
        $this->assertEqualsButNotSame($bar, $fooClone->bar);

        $barClone = deep_clone($bar);

        $this->assertEqualsButNotSame($bar, $barClone);
        $this->assertEqualsButNotSame($foo, $barClone->foo);
    }

    public function testDeepCloneArrays(): void
    {
        $foo = new stdClass();
        $bar = new stdClass();

        $arr1 = [$foo];
        $arr2 = [$bar];

        $foo->name = 'foo';
        $foo->bar = $bar;

        $bar->name = 'bar';
        $bar->foo = $foo;

        $fooClone = deep_clone($arr1)[0];

        $this->assertEqualsButNotSame($foo, $fooClone);
        $this->assertEqualsButNotSame($bar, $fooClone->bar);

        $barClone = deep_clone($arr2)[0];

        $this->assertEqualsButNotSame($bar, $barClone);
        $this->assertEqualsButNotSame($foo, $barClone->foo);
    }

    public function testDeepCloneClosure(): void
    {
        $foo = new stdClass();
        $bar = new stdClass();

        $c1 = function () use ($foo) {
            return $foo;
        };

        $foo->name = 'foo';
        $foo->bar = $bar;

        $bar->name = 'bar';
        $bar->foo = $foo;

        $fooClone = deep_clone($c1)();

        static::assertSame($foo, $fooClone);
        static::assertSame($bar, $fooClone->bar);
    }

    public function provideScalarValues()
    {
        return [
            [null],
            ['null'],
            [0],
            [1],
            [-1],
            [0.5],
            [-0.5],
            ['string'],
            [''],
            [true],
            [false],
        ];
    }

    private function assertEqualsButNotSame($expected, $value): void
    {
        static::assertEquals($expected, $value);
        static::assertNotSame($expected, $value);
    }
}
