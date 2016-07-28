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
 * @covers Nelmio\Alice\Definition\Value\ParameterValue
 */
class ParameterValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(ParameterValue::class, ValueInterface::class, true));
    }

    public function testAccessors()
    {
        $parameterKey = 'dummy_param';
        $value = new ParameterValue($parameterKey);

        $this->assertEquals($parameterKey, $value->getValue());

        $parameterKey = new \stdClass();
        $value = new ParameterValue($parameterKey);

        $this->assertEquals($parameterKey, $value->getValue());
    }

    public function testIsImmutable()
    {
        $value = new ParameterValue(new \stdClass());

        $this->assertNotSame($value->getValue(), $value->getValue());
    }

    public function testIsDeepClonable()
    {
        $reflClass = new \ReflectionClass(ParameterValue::class);
        $parameterKeyRefl = $reflClass->getProperty('parameterKey');
        $parameterKeyRefl->setAccessible(true);

        $parameterKey = 'scalar';

        $value = new ParameterValue($parameterKey);
        $clone = clone $value;

        $this->assertEquals($clone, $value);
        $this->assertNotSame($clone, $value);

        $parameterKey = new \stdClass();

        $value = new ParameterValue($parameterKey);
        $clone = clone $value;

        $this->assertEquals($clone, $value);
        $this->assertNotSame($clone, $value);

        $this->assertNotSame($parameterKeyRefl->getValue($value), $parameterKeyRefl->getValue($clone));
    }
}
