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
 * @covers \Nelmio\Alice\Definition\Value\DynamicArrayValue
 */
class DynamicArrayValueTest extends TestCase
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

    public function testCanBeCastedIntoAString()
    {
        $value = new DynamicArrayValue(10, 'foo');
        $this->assertEquals('10x foo', (string) $value);

        $value = new DynamicArrayValue(new DummyValue('10'), new DummyValue('foo'));
        $this->assertEquals('10x foo', (string) $value);
    }

    public function provideInputTypes()
    {
        yield 'null/array' => [
            null,
            'dummy_element',
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"NULL" instead.'
        ];

        yield 'bool/array' => [
            true,
            'dummy_element',
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"boolean" instead.'
        ];

        yield 'string/array' => [
            '',
            'dummy_element',
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"string" instead.'
        ];

        yield 'float/array' => [
            .5,
            'dummy_element',
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"double" instead.'
        ];

        yield 'array/array' => [
            [],
            'dummy_element',
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"array" instead.'
        ];

        yield 'object/array' => [
            new \stdClass(),
            'dummy_element',
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"stdClass" instead.'
        ];

        yield 'closure/array' => [
            function () {
            },
            'dummy_element',
            'Expected quantifier to be either an integer or a "'.ValueInterface::class.'". Got '
            .'"Closure" instead.'
        ];

        yield 'int/null' => [
            -1,
            null,
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"NULL" instead.'
        ];

        yield 'int/bool' => [
            -1,
            true,
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"boolean" instead.'
        ];

        yield 'int/float' => [
            -1,
            .5,
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"double" instead.'
        ];

        yield 'int/int' => [
            1,
            1,
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"integer" instead.'
        ];

        yield 'int/closure' => [
            -1,
            function () {
            },
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"Closure" instead.'
        ];

        yield 'int/non value interface object' => [
            -1,
            new \stdClass(),
            'Expected element to be either string, an array or a "'.ValueInterface::class.'". Got '
            .'"stdClass" instead.'
        ];
    }

    public function provideValues()
    {
        yield 'int value' => [-1, 'string', -1];
        yield 'object value' => [new FakeValue(), new FakeValue(), new FakeValue()];
    }
}
