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
 * @covers Nelmio\Alice\Definition\Value\FixturePropertyValue
 */
class FixturePropertyValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(FixturePropertyValue::class, ValueInterface::class, true));
    }

    public function testAccessors()
    {
        $reference = new FixtureReferenceValue('user0');
        $property = 'username';

        $value = new FixturePropertyValue($reference, $property);

        $this->assertEquals($reference, $value->getReference());
        $this->assertEquals($property, $value->getProperty());
        $this->assertEquals([$reference, $property], $value->getValue());
    }

    public function testIsImmutable()
    {
        $reference = new FixtureReferenceValue('user0');
        $property = 'username';

        $value = new FixturePropertyValue($reference, $property);

        $this->assertNotSame($value->getReference(), $value->getReference());
        $this->assertNotSame($value->getValue(), $value->getValue());
    }

    public function testIsDeepClonable()
    {
        $reference = new FixtureReferenceValue('user0');
        $property = 'username';
        $value = new FixturePropertyValue($reference, $property);

        $reflClass = new \ReflectionClass(FixturePropertyValue::class);
        $referenceRefl = $reflClass->getProperty('reference');
        $referenceRefl->setAccessible(true);

        $clone = clone $value;

        $this->assertEquals($clone, $value);
        $this->assertNotSame($clone, $value);

        $this->assertNotSame($referenceRefl->getValue($value), $referenceRefl->getValue($clone));
    }
}
