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
 * @covers Nelmio\Alice\Definition\Value\DynamicArrayValue
 */
class DynamicArrayValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(UniqueValue::class, ValueInterface::class, true));
    }

    /**
     * @dataProvider provideValues
     */
    public function testAccessors($quantifier, $element, $expectedQuantifier)
    {
        $value = new DynamicArrayValue($quantifier, $element);

        $this->assertEquals($expectedQuantifier, $value->getQuantifier());
        $this->assertEquals($element, $value->getElement());
        $this->assertEquals([$expectedQuantifier, $element], $value->getValue());
    }

    public function testIsImmutable()
    {
        $value = new DynamicArrayValue(new \stdClass(), new \stdClass());

        $this->assertNotSame($value->getQuantifier(), $value->getQuantifier());
        $this->assertNotSame($value->getElement(), $value->getElement());
        $this->assertNotSame($value->getValue(), $value->getValue());
    }

    public function testIsDeepClonable()
    {
        $quantifier = '10';
        $element = '@user0';
        $valueWithScalars = new DynamicArrayValue($quantifier, $element);
        $clone = clone $valueWithScalars;

        $this->assertEquals($valueWithScalars, $clone);
        $this->assertNotSame($valueWithScalars, $clone);

        $quantifier = new \stdClass();
        $element = new \stdClass();
        $valueWithObjects = new DynamicArrayValue($quantifier, $element);
        $clone = clone $valueWithObjects;

        $this->assertEquals($valueWithObjects, $clone);
        $this->assertNotSame($valueWithObjects, $clone);
    }

    public function provideValues()
    {
        yield 'null value' => [null, null, 0];
        yield 'string value' => ['string', 'string', 0];
        yield 'object value' => [new \stdClass(), new \stdClass(), new \stdClass()];
    }
}
