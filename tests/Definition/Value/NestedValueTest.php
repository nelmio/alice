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
 * @covers \Nelmio\Alice\Definition\Value\NestedValue
 * @internal
 */
class NestedValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        self::assertTrue(is_a(NestedValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $list = [];
        $value = new NestedValue($list);

        self::assertEquals($list, $value->getValue());

        $list = [new stdClass()];
        $value = new NestedValue($list);

        self::assertEquals($list, $value->getValue());
    }

    public function testIsNotImmutable(): void
    {
        $value = new NestedValue([
            $arg0 = new stdClass(),
        ]);

        self::assertSame($arg0, $value->getValue()[0]);
    }

    public function testCanBeCastedIntoAString(): void
    {
        self::assertEquals("(nested) array (\n)", (string) (new NestedValue([])));
    }
}
