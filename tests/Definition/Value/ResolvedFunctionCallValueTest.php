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
 * @covers \Nelmio\Alice\Definition\Value\ResolvedFunctionCallValue
 * @internal
 */
final class ResolvedFunctionCallValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        self::assertTrue(is_a(ResolvedFunctionCallValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $name = 'setUsername';
        $arguments = [new stdClass()];

        $value = new ResolvedFunctionCallValue($name, $arguments);

        self::assertEquals($name, $value->getName());
        self::assertEquals($arguments, $value->getArguments());
        self::assertEquals([$name, $arguments], $value->getValue());
    }

    public function testIsMutable(): void
    {
        $arguments = [
            $arg0 = new stdClass(),
        ];
        $value = new ResolvedFunctionCallValue('setUsername', $arguments);

        // Mutate injected value
        $arg0->foo = 'bar';

        self::assertEquals($arg0->foo, $value->getArguments()[0]->foo);
        self::assertSame($arg0, $value->getArguments()[0]);

        self::assertNotEquals(
            [
                new stdClass(),
            ],
            $value->getArguments(),
        );
        self::assertNotEquals(
            [
                'setUsername',
                [new stdClass()],
            ],
            $value->getValue(),
        );
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new ResolvedFunctionCallValue('foo');
        self::assertEquals('<foo()>', (string) $value);

        $value = new ResolvedFunctionCallValue('foo', ['bar']);
        self::assertEquals("<foo(array (\n  0 => 'bar',\n))>", (string) $value);
    }
}
