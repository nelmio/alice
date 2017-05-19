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
 * @covers \Nelmio\Alice\Definition\Value\ParameterValue
 */
class ParameterValueTest extends TestCase
{
    public function testIsAValue()
    {
        $this->assertTrue(is_a(ParameterValue::class, ValueInterface::class, true));
    }

    /**
     * @dataProvider provideInputValues
     */
    public function testThrowsErrorIfInvalidTypeGiven($value, $errorMessage)
    {
        try {
            new ParameterValue($value);
            $this->fail('Expected error to be thrown.');
        } catch (\TypeError $error) {
            $this->assertEquals($errorMessage, $error->getMessage());
        }
    }

    public function testReadAccessorsReturnPropertiesValues()
    {
        $parameterKey = 'dummy_param';
        $value = new ParameterValue($parameterKey);

        $this->assertEquals($parameterKey, $value->getValue());

        $parameterKey = new FakeValue();
        $value = new ParameterValue($parameterKey);

        $this->assertEquals($parameterKey, $value->getValue());
    }

    public function testIsImmutable()
    {
        $injectedValue = new MutableValue('v0');
        $value = new ParameterValue($injectedValue);

        // Mutate injected value
        $injectedValue->setValue('v1');

        // Mutate returned value
        $value->getValue()->setValue('v2');

        $this->assertNotSame(new MutableValue('v0'), $value->getValue());
    }

    public function testCanBeCastedIntoAString()
    {
        $value = new ParameterValue('foo');
        $this->assertEquals('<{foo}>', $value);

        $value = new ParameterValue(
            new DummyValue('foo')
        );
        $this->assertEquals('<{foo}>', $value);
    }

    public function provideInputValues()
    {
        yield 'null' => [
            null,
            'Expected parameter key to be either a string or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "NULL" instead.',
        ];

        yield 'array' => [
            [],
            'Expected parameter key to be either a string or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "array" instead.',
        ];

        yield 'stdClass' => [
            new \stdClass(),
            'Expected parameter key to be either a string or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "stdClass" instead.',
        ];
    }
}
