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
 * @covers \Nelmio\Alice\Definition\Value\OptionalValue
 */
class OptionalValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        static::assertTrue(is_a(OptionalValue::class, ValueInterface::class, true));
    }

    /**
     * @dataProvider provideInputValues
     */
    public function testThrowsErrorIfInvalidTypeGiven($quantifier, $firstMember, $secondMember, $errorMessage): void
    {
        try {
            new OptionalValue($quantifier, $firstMember, $secondMember);
            static::fail('Expected error to be thrown.');
        } catch (TypeError $error) {
            static::assertEquals($errorMessage, $error->getMessage());
        }
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $quantifier = 50;
        $firstMember = 'first';
        $secondMember = 'second';

        $value = new OptionalValue($quantifier, $firstMember, $secondMember);

        static::assertEquals($quantifier, $value->getQuantifier());
        static::assertEquals($firstMember, $value->getFirstMember());
        static::assertEquals($secondMember, $value->getSecondMember());
        static::assertEquals([$quantifier, $firstMember, $secondMember], $value->getValue());

        $quantifier = new FakeValue();
        $firstMember = new FakeValue();
        $secondMember = new FakeValue();

        $value = new OptionalValue($quantifier, $firstMember, $secondMember);

        static::assertEquals($quantifier, $value->getQuantifier());
        static::assertEquals($firstMember, $value->getFirstMember());
        static::assertEquals($secondMember, $value->getSecondMember());
        static::assertEquals([$quantifier, $firstMember, $secondMember], $value->getValue());

        $quantifier = '100';
        $firstMember = new FakeValue();
        $secondMember = null;

        $value = new OptionalValue($quantifier, $firstMember, $secondMember);

        static::assertEquals(100, $value->getQuantifier());
        static::assertEquals($firstMember, $value->getFirstMember());
        static::assertEquals($secondMember, $value->getSecondMember());
        static::assertEquals([$quantifier, $firstMember, $secondMember], $value->getValue());
    }

    public function testIsImmutable(): void
    {
        $quantifier = new MutableValue('q0');
        $firstMember = new MutableValue('f0');
        $secondMember = new MutableValue('s0');
        $value = new OptionalValue($quantifier, $firstMember, $secondMember);

        // Mutate injected values
        $quantifier->setValue('q1');
        $firstMember->setValue('f1');
        $secondMember->setValue('s1');

        // Mutate returned values
        $value->getQuantifier()->setValue('q2');
        $value->getFirstMember()->setValue('f2');
        $value->getSecondMember()->setValue('s2');

        static::assertNotSame(new MutableValue('q0'), $value->getQuantifier());
        static::assertNotSame(new MutableValue('f0'), $value->getFirstMember());
        static::assertNotSame(new MutableValue('s0'), $value->getSecondMember());
        static::assertNotSame(
            [
                new MutableValue('q0'),
                new MutableValue('f0'),
                new MutableValue('s0'),
            ],
            $value->getValue()
        );
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new OptionalValue(10, 'foo');
        static::assertEquals('10%? foo : null', (string) $value);

        $value = new OptionalValue(10, 'foo', 'bar');
        static::assertEquals('10%? foo : bar', (string) $value);

        $value = new OptionalValue(new DummyValue('10'), new DummyValue('foo'));
        static::assertEquals('10%? foo : null', (string) $value);
    }

    public function provideInputValues()
    {
        yield 'null/string/string' => [
            null,
            'first_member',
            'second_member',
            'Expected quantifier to be either a scalar value or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "NULL" instead.',
        ];

        yield 'array/string/string' => [
            [],
            'first_member',
            'second_member',
            'Expected quantifier to be either a scalar value or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "array" instead.',
        ];

        yield 'stdClass/string/string' => [
            new stdClass(),
            'first_member',
            'second_member',
            'Expected quantifier to be either a scalar value or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "stdClass" instead.',
        ];

        yield 'string/null/string' => [
            'quantifier',
            null,
            'second_member',
            'Expected first member to be either a string or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "NULL" instead.',
        ];

        yield 'string/array/string' => [
            'quantifier',
            [],
            'second_member',
            'Expected first member to be either a string or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "array" instead.',
        ];

        yield 'string/stdClass/string' => [
            'quantifier',
            new stdClass(),
            'second_member',
            'Expected first member to be either a string or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "stdClass" instead.',
        ];

        yield 'string/string/array' => [
            'quantifier',
            'first_member',
            [],
            'Expected second member to be either null, a string or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "array" instead.',
        ];

        yield 'string/string/stdClass' => [
            'quantifier',
            'first_member',
            new stdClass(),
            'Expected second member to be either null, a string or an instance of "Nelmio\Alice\Definition\ValueInterface". '
            .'Got "stdClass" instead.',
        ];
    }
}
