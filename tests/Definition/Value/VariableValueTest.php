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
 * @covers \Nelmio\Alice\Definition\Value\VariableValue
 */
class VariableValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(VariableValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $variable = 'username';
        $value = new VariableValue($variable);

        $this->assertEquals($variable, $value->getValue());
    }

    public function testIsCastableIntoAString()
    {
        $value = new VariableValue('username');

        $this->assertEquals('$username', $value);
    }
}
