<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;

/**
 * @covers Nelmio\Alice\Definition\Value\FunctionCallValue
 */
class FunctionCallValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(FunctionCallValue::class, ValueInterface::class, true));
    }

    public function testAccessors()
    {
        $name = 'setUsername';
        $arguments = [new \stdClass()];

        $value = new FunctionCallValue($name, $arguments);

        $this->assertEquals($name, $value->getName());
        $this->assertEquals($arguments, $value->getArguments());
        $this->assertEquals([$name, $arguments], $value->getValue());
    }
}
