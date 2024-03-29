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

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Definition\Value\ArrayValue
 * @internal
 */
class ArrayValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        self::assertTrue(is_a(ArrayValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnValues(): void
    {
        $list = [];
        $value = new ArrayValue($list);

        self::assertEquals($list, $value->getValue());

        $list = [new stdClass()];
        $value = new ArrayValue($list);

        self::assertEquals($list, $value->getValue());
    }

    public function testIsImmutable(): void
    {
        $value = new ArrayValue([
            $std = new stdClass(),
        ]);

        // Mutate input value
        $std->foo = 'bar';

        // Mutate retrieved value
        $value->getValue()[0]->foo = 'baz';

        self::assertEquals(
            [
                new stdClass(),
            ],
            $value->getValue(),
        );
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new ArrayValue([]);
        self::assertEquals("array (\n)", (string) $value);

        $value = new ArrayValue(['foo', 'bar']);
        self::assertEquals("array (\n  0 => 'foo',\n  1 => 'bar',\n)", (string) $value);
    }
}
