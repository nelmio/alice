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
 * @covers \Nelmio\Alice\Definition\Value\FixturePropertyValue
 */
class FixturePropertyValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(FixturePropertyValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $reference = new FakeValue();
        $property = 'username';

        $value = new FixturePropertyValue($reference, $property);

        $this->assertEquals($reference, $value->getReference());
        $this->assertEquals($property, $value->getProperty());
        $this->assertEquals([$reference, $property], $value->getValue());
    }

    /**
     * @depends Nelmio\Alice\Definition\Value\FixtureReferenceValueTest::testIsImmutable
     */
    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }

    public function testIsCastableIntoAString()
    {
        $value = new FixturePropertyValue(
            new FixtureReferenceValue('dummy'),
            'foo'
        );

        $this->assertEquals('@dummy->foo', (string) $value);
    }
}
