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
 * @covers \Nelmio\Alice\Definition\Value\DynamicArrayValue
 */
class DynamicArrayValueTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(UniqueValue::class, ValueInterface::class, true));
    }

    /**
     * @dataProvider provideInputTypes
     */
    public function testThrowsErrorIfInvalidInputType($quantifier, $element, $errorMessage)
    {
        try {
            new DynamicArrayValue($quantifier, $element);
            $this->fail('Expected error to be thrown.');
        } catch (\TypeError $error) {
            $this->assertEquals($errorMessage, $error->getMessage());
        }
    }

    /**
     * @dataProvider provideValues
     */
    public function testReadAccessorsReturnPropertiesValues($quantifier, $element, $expectedQuantifier)
    {
        $value = new DynamicArrayValue($quantifier, $element);

        $this->assertEquals($expectedQuantifier, $value->getQuantifier());
        $this->assertEquals($element, $value->getElement());
        $this->assertEquals([$expectedQuantifier, $element], $value->getValue());
    }

    public function testIsImmutable()
    {
        $quantifier = new MutableValue('q0');
        $elementValue = new MutableValue('e0');
        $value = new DynamicArrayValue($quantifier, $elementValue);

        // Mutate injected values
        $quantifier->setValue('q1');
        $elementValue->setValue('e1');

        // Mutate returned values
        $value->getQuantifier()->setValue('q2');
        $value->getElement()->setValue('e2');

        $this->assertEquals(new MutableValue('q0'), $value->getQuantifier());
        $this->assertEquals(new MutableValue('e0'), $value->getElement());
        $this->assertEquals(
            [
                new MutableValue('q0'),
                new MutableValue('e0'),
            ],
            $value->getValue()
        );
    }

    public function testIsCastableIntoAString()
    {
        $value = new DynamicArrayValue(10, 'foo');
        $this->assertEquals('10x foo', (string) $value);

        $value = new DynamicArrayValue(new DummyValue('10'), new DummyValue('foo'));
        $this->assertEquals('10x foo', (string) $value);
    }

    public function provideInputTypes()
    {
        yield 'null/string' => [
            null,
            'dummy_element',
            'Expected quantifier to be either a scalar value or a "Nelmio\Alice\Definition\ValueInterface" object. Got '
            .'"NULL" instead.'
        ];

        yield 'array/string' => [
            [],
            'dummy_element',
            'Expected quantifier to be either a scalar value or a "Nelmio\Alice\Definition\ValueInterface" object. Got'
            .' "array" instead.'
        ];

        yield 'string/null' => [
            'dummy_quantifier',
            null,
            'Expected element to be either string, an array or a "Nelmio\Alice\Definition\ValueInterface" object. Got "NULL" '
            .'instead.'
        ];

        yield 'string/stdClass' => [
            'dummy_quantifier',
            new \stdClass(),
            'Expected element to be either string, an array or a "Nelmio\Alice\Definition\ValueInterface" object. Got "stdClass" '
            .'instead.'
        ];
    }

    public function provideValues()
    {
        yield 'string value' => ['string', 'string', 0];
        yield 'string numeric value' => ['100', 'string', 100];
        yield 'object value' => [new FakeValue(), new FakeValue(), new FakeValue()];
    }
}
