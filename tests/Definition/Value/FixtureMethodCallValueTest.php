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
 * @covers \Nelmio\Alice\Definition\Value\FixtureMethodCallValue
 */
class FixtureMethodCallValueTest extends TestCase
{
    public function testIsAValue(): void
    {
        static::assertTrue(is_a(FixtureMethodCallValue::class, ValueInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $reference = new FakeValue();
        $function = new FunctionCallValue('getName');

        $value = new FixtureMethodCallValue($reference, $function);

        static::assertEquals($reference, $value->getReference());
        static::assertEquals($function, $value->getFunctionCall());
        static::assertEquals([$reference, $function], $value->getValue());
    }

    /**
     * @depends \Nelmio\Alice\Definition\ServiceReference\FixtureReferenceTest::testIsImmutable
     * @depends \Nelmio\Alice\Definition\Value\FunctionCallValueTest::testIsImmutable
     */
    public function testIsImmutable(): void
    {
        static::assertTrue(true, 'Nothing to do.');
    }

    public function testCanBeCastedIntoAString(): void
    {
        $value = new FixtureMethodCallValue(
            new FixtureReferenceValue('dummy'),
            new FunctionCallValue('foo')
        );
        static::assertEquals('@dummy->foo()', (string) $value);

        $value = new FixtureMethodCallValue(
            new FixtureReferenceValue('dummy'),
            new FunctionCallValue('foo', ['bar'])
        );
        static::assertEquals("@dummy->foo(array (\n  0 => 'bar',\n))", (string) $value);
    }
}
