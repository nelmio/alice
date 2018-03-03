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

/**
 * @covers \Nelmio\Alice\Definition\Value\ResolvedFunctionCallValue
 */
class ResolvedFunctionCallValueTest extends TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(ResolvedFunctionCallValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $name = 'setUsername';
        $arguments = [new \stdClass()];

        $value = new ResolvedFunctionCallValue($name, $arguments);

        $this->assertEquals($name, $value->getName());
        $this->assertEquals($arguments, $value->getArguments());
        $this->assertEquals([$name, $arguments], $value->getValue());
    }

    public function testIsMutable()
    {
        $arguments = [
            $arg0 = new \stdClass(),
        ];
        $value = new ResolvedFunctionCallValue('setUsername', $arguments);

        // Mutate injected value
        $arg0->foo = 'bar';

        $this->assertEquals($arg0->foo, $value->getArguments()[0]->foo);
        $this->assertSame($arg0, $value->getArguments()[0]);

        $this->assertNotEquals(
            [
                new \stdClass(),
            ],
            $value->getArguments()
        );
        $this->assertNotEquals(
            [
                'setUsername',
                [new \stdClass()],
            ],
            $value->getValue()
        );
    }

    public function testCanBeCastedIntoAString()
    {
        $value = new ResolvedFunctionCallValue('foo');
        $this->assertEquals('<foo()>', (string) $value);

        $value = new ResolvedFunctionCallValue('foo', ['bar']);
        $this->assertEquals("<foo(array (\n  0 => 'bar',\n))>", (string) $value);
    }
}
