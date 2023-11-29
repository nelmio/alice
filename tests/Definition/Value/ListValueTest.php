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
 * @covers \Nelmio\Alice\Definition\Value\ListValue
 * @internal
 */
class ListValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        self::assertTrue(is_a(ListValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $list = [];
        $value = new ListValue($list);

        self::assertEquals($list, $value->getValue());

        $list = [new stdClass()];
        $value = new ListValue($list);

        self::assertEquals($list, $value->getValue());
    }

    public function testIsImmutable(): void
    {
        $value = new ListValue([
            $arg0 = new stdClass(),
        ]);

        // Mutate injected value
        $arg0->foo = 'bar';

        // Mutate returned value
        // @phpstan-ignore-next-line
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
        $value = new ListValue(['a', 'b', new DummyValue('c')]);
        self::assertEquals('abc', (string) $value);
    }
}
