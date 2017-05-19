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
 * @covers \Nelmio\Alice\Definition\Value\FunctionCallValue
 */
class FunctionCallValueTest extends TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(FunctionCallValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $name = 'setUsername';
        $arguments = [new \stdClass()];

        $value = new FunctionCallValue($name, $arguments);

        $this->assertEquals($name, $value->getName());
        $this->assertEquals($arguments, $value->getArguments());
        $this->assertEquals([$name, $arguments], $value->getValue());
    }

    public function testIsImmutable()
    {
        $arguments = [
            $arg0 = new \stdClass(),
        ];
        $value = new FunctionCallValue('setUsername', $arguments);

        // Mutate injected value
        $arg0->foo = 'bar';

        // Mutate returned value
        $value->getArguments()[0]->foo = 'baz';

        $this->assertEquals(
            [
                new \stdClass(),
            ],
            $value->getArguments()
        );
        $this->assertEquals(
            [
                'setUsername',
                [new \stdClass()],
            ],
            $value->getValue()
        );
    }

    public function testCanBeCastedIntoAString()
    {
        $value = new FunctionCallValue('foo');
        $this->assertEquals('<foo()>', (string) $value);

        $value = new FunctionCallValue('foo', ['bar']);
        $this->assertEquals("<foo(array (\n  0 => 'bar',\n))>", (string) $value);
    }
}
