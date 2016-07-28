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
 * @covers Nelmio\Alice\Definition\Value\FixtureMethodCallValue
 */
class FixtureMethodCallValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(FixtureMethodCallValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $reference = new FixtureReferenceValue('user0');
        $function = new FunctionCallValue('getName');

        $value = new FixtureMethodCallValue($reference, $function);

        $this->assertEquals($reference, $value->getReference());
        $this->assertEquals($function, $value->getFunctionCall());
        $this->assertEquals([$reference, $function], $value->getValue());
    }

    public function testIsImmutable()
    {
        $reference = new FixtureReferenceValue('user0');
        $function = new FunctionCallValue('getName');

        $value = new FixtureMethodCallValue($reference, $function);

        $this->assertNotSame($value->getReference(), $value->getReference());
        $this->assertNotSame($value->getFunctionCall(), $value->getFunctionCall());
        $this->assertNotSame($value->getValue(), $value->getValue());
    }

    public function testIsDeepClonable()
    {
        $reference = new FixtureReferenceValue('user0');
        $function = new FunctionCallValue('getName');
        $value = new FixtureMethodCallValue($reference, $function);

        $reflClass = new \ReflectionClass(FixtureMethodCallValue::class);
        $referenceRefl = $reflClass->getProperty('reference');
        $referenceRefl->setAccessible(true);
        $functionRefl = $reflClass->getProperty('function');
        $functionRefl->setAccessible(true);

        $clone = clone $value;

        $this->assertEquals($clone, $value);
        $this->assertNotSame($clone, $value);

        $this->assertNotSame($referenceRefl->getValue($value), $referenceRefl->getValue($clone));
        $this->assertNotSame($functionRefl->getValue($value), $functionRefl->getValue($clone));
    }
}
