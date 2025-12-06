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
use stdClass;
use TypeError;

/**
 * @covers \Nelmio\Alice\Definition\Value\ParameterValue
 * @internal
 */
final class ParameterValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        self::assertTrue(is_a(ParameterValue::class, ValueInterface::class, true));
    }

    /**
     * @dataProvider provideInputValues
     */
    public function testThrowsErrorIfInvalidTypeGiven($value, $errorMessage): void
    {
        try {
            new ParameterValue($value);
            self::fail('Expected error to be thrown.');
        } catch (TypeError $error) {
            self::assertEquals($errorMessage, $error->getMessage());
        }
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $parameterKey = 'dummy_param';
        $value = new ParameterValue($parameterKey);

        self::assertEquals($parameterKey, $value->getValue());

        $parameterKey = new FakeValue();
        $value = new ParameterValue($parameterKey);

        self::assertEquals($parameterKey, $value->getValue());
    }

    public function testIsImmutable(): void
    {
        $injectedValue = new MutableValue('v0');
        $value = new ParameterValue($injectedValue);

        // Mutate injected value
        $injectedValue->setValue('v1');

        // Mutate returned value
        $value->getValue()->setValue('v2');

        self::assertNotSame(new MutableValue('v0'), $value->getValue());
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new ParameterValue('foo');
        self::assertEquals('<{foo}>', $value);

        $value = new ParameterValue(
            new DummyValue('foo'),
        );
        self::assertEquals('<{foo}>', $value);
    }

    public static function provideInputValues(): iterable
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
            new stdClass(),
            'Expected parameter key to be either a string or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "stdClass" instead.',
        ];
    }
}
