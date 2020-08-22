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
 * @covers \Nelmio\Alice\Definition\Value\FunctionCallValue
 */
class FunctionCallValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        static::assertTrue(is_a(FunctionCallValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $name = 'setUsername';
        $arguments = [new stdClass()];

        $value = new FunctionCallValue($name, $arguments);

        static::assertEquals($name, $value->getName());
        static::assertEquals($arguments, $value->getArguments());
        static::assertEquals([$name, $arguments], $value->getValue());
    }

    public function testIsImmutable(): void
    {
        $arguments = [
            $arg0 = new stdClass(),
        ];
        $value = new FunctionCallValue('setUsername', $arguments);

        // Mutate injected value
        $arg0->foo = 'bar';

        // Mutate returned value
        $value->getArguments()[0]->foo = 'baz';

        static::assertEquals(
            [
                new stdClass(),
            ],
            $value->getArguments()
        );
        static::assertEquals(
            [
                'setUsername',
                [new stdClass()],
            ],
            $value->getValue()
        );
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new FunctionCallValue('foo');
        static::assertEquals('<foo()>', (string) $value);

        $value = new FunctionCallValue('foo', ['bar']);
        static::assertEquals("<foo(array (\n  0 => 'bar',\n))>", (string) $value);
    }
}
